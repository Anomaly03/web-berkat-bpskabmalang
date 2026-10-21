<?php

namespace App\Services;

use fivefilters\Readability\Configuration;
use fivefilters\Readability\ParseException;
use fivefilters\Readability\Readability;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleExtractorService
{
    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

    /**
     * Ambil isi artikel dari link hasil Google News.
     *
     * @return array{url_asli:string, domain:string, penulis:?string, paragraf:array<int,string>}
     * @throws \RuntimeException dengan pesan yang aman ditampilkan ke pengguna
     */
    public function ekstrak(string $linkGoogle): array
    {
        try {
            // Cache 6 jam: klik ulang artikel yang sama tidak menembak situs lagi.
            // Kalau gagal (exception), hasilnya tidak ikut di-cache.
            return Cache::remember('artikel:' . md5($linkGoogle), now()->addHours(6), function () use ($linkGoogle) {
                $urlAsli = $this->resolveUrl($linkGoogle);

                if ($urlAsli === null) {
                    throw new \RuntimeException('Alamat asli artikel tidak dapat diambil dari Google News.');
                }

                return $this->parse($this->ambilHtml($urlAsli), $urlAsli);
            });
        } catch (ConnectionException $e) {
            throw new \RuntimeException('Koneksi ke situs sumber gagal atau terlalu lama.');
        }
    }

    /* ---------------------------------------------------------------
     |  1. Link Google News -> alamat artikel asli
     * --------------------------------------------------------------- */

    private function resolveUrl(string $url): ?string
    {
        $host = (string) parse_url($url, PHP_URL_HOST);

        if (! str_contains($host, 'news.google.com')) {
            return $url;
        }

        $id = basename((string) parse_url($url, PHP_URL_PATH));

        if ($id === '') {
            return null;
        }

        return $this->decodeOffline($id) ?? $this->decodeViaGoogle($id);
    }

    /** Link gaya lama: alamat asli tertanam langsung di token base64. */
    private function decodeOffline(string $id): ?string
    {
        $bin = base64_decode(strtr($id, '-_', '+/'));

        if ($bin === false || strlen($bin) < 5) {
            return null;
        }

        if (str_starts_with($bin, "\x08\x13\x22")) {
            $bin = substr($bin, 3);
        }
        if (str_ends_with($bin, "\xd2\x01\x00")) {
            $bin = substr($bin, 0, -3);
        }

        $len = ord($bin[0]);

        if ($len >= 0x80) {
            $len = ($len & 0x7f) | (ord($bin[1]) << 7);
            $kandidat = substr($bin, 2, $len);
        } else {
            $kandidat = substr($bin, 1, $len);
        }

        return preg_match('#^https?://#i', $kandidat) ? $kandidat : null;
    }

    /** Link gaya baru: harus ditanyakan ke endpoint internal Google (tidak resmi). */
    private function decodeViaGoogle(string $id): ?string
    {
        $halaman = Http::timeout(8)
            ->withHeaders(['User-Agent' => self::UA, 'Accept-Language' => 'id-ID,id;q=0.9'])
            ->get("https://news.google.com/rss/articles/{$id}", ['hl' => 'id', 'gl' => 'ID', 'ceid' => 'ID:id']);

        if ($halaman->failed()) {
            return null;
        }

        $body = $halaman->body();

        if (! preg_match('/data-n-a-sg="([^"]+)"/', $body, $sg)
            || ! preg_match('/data-n-a-ts="([^"]+)"/', $body, $ts)) {
            Log::warning('Google News: signature artikel tidak ditemukan', ['id' => $id]);

            return null;
        }

        $dalam = json_encode([
            'garturlreq',
            [
                ['X', 'X', ['X', 'X'], null, null, 1, 1, 'US:en', null, 1, null, null, null, null, null, 0, 1],
                'X', 'X', 1, [1, 1, 1], 1, 1, null, 0, 0, null, 0,
            ],
            $id,
            (int) $ts[1],
            $sg[1],
        ], JSON_UNESCAPED_SLASHES);

        $respons = Http::timeout(8)
            ->asForm()
            ->withHeaders(['User-Agent' => self::UA, 'Referer' => 'https://news.google.com/'])
            ->post('https://news.google.com/_/DotsSplashUi/data/batchexecute', [
                'f.req' => json_encode([[['Fbv4je', $dalam]]], JSON_UNESCAPED_SLASHES),
            ]);

        if ($respons->failed()) {
            return null;
        }

        foreach (preg_split('/\R/', $respons->body()) as $baris) {
            if (! str_starts_with($baris, '[[')) {
                continue;
            }

            foreach (json_decode($baris, true) ?? [] as $entri) {
                if (($entri[0] ?? null) === 'wrb.fr' && ($entri[1] ?? null) === 'Fbv4je') {
                    $isi = json_decode($entri[2] ?? '', true);
                    $url = $isi[1] ?? null;

                    return is_string($url) && str_starts_with($url, 'http') ? $url : null;
                }
            }
        }

        Log::warning('Google News: respons batchexecute tidak berisi URL', ['id' => $id]);

        return null;
    }

    /* ---------------------------------------------------------------
     |  2. Ambil halaman artikel
     * --------------------------------------------------------------- */

    private function ambilHtml(string $url): string
    {
        $res = Http::timeout(12)
            ->withHeaders([
                'User-Agent'      => self::UA,
                'Accept'          => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
            ])
            ->get($url);

        if ($res->failed()) {
            throw new \RuntimeException('Situs sumber menolak atau tidak dapat diakses (HTTP ' . $res->status() . ').');
        }

        $tipe = strtolower((string) $res->header('Content-Type'));

        if ($tipe !== '' && ! str_contains($tipe, 'html')) {
            throw new \RuntimeException('Tautan tidak mengarah ke halaman artikel biasa.');
        }

        $html = $res->body();

        if (! mb_check_encoding($html, 'UTF-8')) {
            $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
        }

        return $html;
    }

    /* ---------------------------------------------------------------
     |  3. HTML -> paragraf bersih
     * --------------------------------------------------------------- */

    private function parse(string $html, string $url): array
    {
        $readability = new Readability(new Configuration());

        try {
            // Versi library ini mengembalikan objek Article (data dibaca lewat properti)
            $artikel = $readability->parse($html);
        } catch (ParseException $e) {
            throw new \RuntimeException('Isi artikel tidak dapat dibaca (kemungkinan memerlukan JavaScript atau berbayar).');
        }

        $paragraf = $this->keParagraf((string) $artikel->content);

        if (count($paragraf) < 2) {
            throw new \RuntimeException('Isi artikel terlalu singkat atau tidak terbaca (kemungkinan berbayar).');
        }

        return [
            'url_asli' => $url,
            'domain'   => preg_replace('/^www\./', '', (string) parse_url($url, PHP_URL_HOST)),
            'penulis'  => $artikel->byline ? trim((string) $artikel->byline) : null,
            'paragraf' => array_slice($paragraf, 0, 60),
        ];
    }

    /** Ambil teks tiap <p> dan <li>; baris pendek ("Baca juga:", dll) dibuang. */
    private function keParagraf(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>');

        $hasil = [];

        foreach ((new \DOMXPath($dom))->query('//p | //li') as $node) {
            $teks = trim((string) preg_replace('/\s+/u', ' ', $node->textContent));

            if (mb_strlen($teks) >= 40) {
                $hasil[] = $teks;
            }
        }

        return array_values(array_unique($hasil));
    }
}
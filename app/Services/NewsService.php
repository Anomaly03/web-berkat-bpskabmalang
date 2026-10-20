<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsService
{
    private const BASE_URL = 'https://news.google.com/rss/search';

    /**
     * Cari berita berdasarkan nama kategori + rentang bulan/tahun.
     *
     * @return array<int, array{judul:string, link:string, sumber:string, domain:string, tanggal:string, tanggal_label:string}>
     */
    public function search(string $namaKategori, int $bulan, int $tahun, int $limit = 10): array
    {
        $awal  = Carbon::create($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta');
        $akhir = $awal->copy()->addMonth();

        $keyword = $this->bersihkanNama($namaKategori);
        $query   = sprintf(
            '%s after:%s before:%s',
            $keyword,
            $awal->format('Y-m-d'),
            $akhir->format('Y-m-d')
        );

        // Cache 30 menit supaya klik berulang tidak menembak Google terus
        $cacheKey = 'news:' . md5($query);

        $items = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            return $this->ambilDariRss($query);
        });

        // Filter ulang tanggal di sisi backend (operator Google tidak selalu presisi)
        $items = array_filter($items, function (array $item) use ($awal, $akhir) {
            $tgl = Carbon::parse($item['tanggal']);

            return $tgl->greaterThanOrEqualTo($awal) && $tgl->lessThan($akhir);
        });

        usort($items, fn ($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

        return array_slice(array_values($items), 0, $limit);
    }

    /** Buang awalan penomoran: "A. ", "1. ", "1a. ", "M, N. ", "R, S, T, U. " */
    private function bersihkanNama(string $nama): string
    {
        return trim(preg_replace('/^(?:[A-Z](?:,\s*[A-Z])*|\d+[a-z]?)\.\s+/', '', $nama));
    }

    private function ambilDariRss(string $query): array
    {
        $response = Http::timeout(15)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (BERKAT BPS)'])
            ->get(self::BASE_URL, [
                'q'    => $query,
                'hl'   => 'id',
                'gl'   => 'ID',
                'ceid' => 'ID:id',
            ]);

        if ($response->failed()) {
            Log::warning('Google News RSS gagal', ['status' => $response->status(), 'query' => $query]);
            throw new \RuntimeException('Layanan berita tidak dapat diakses saat ini.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body());

        if ($xml === false) {
            throw new \RuntimeException('Format respons berita tidak dapat dibaca.');
        }

        $hasil = [];

        foreach ($xml->channel->item as $item) {
            $sumber = trim((string) $item->source);
            $judul  = trim((string) $item->title);

            // Judul dari Google formatnya "Judul - Nama Media"
            if ($sumber !== '' && str_ends_with($judul, ' - ' . $sumber)) {
                $judul = substr($judul, 0, -strlen(' - ' . $sumber));
            }

            $tanggal = Carbon::parse((string) $item->pubDate)->setTimezone('Asia/Jakarta');
            $urlSumber = (string) ($item->source['url'] ?? '');

            $hasil[] = [
                'judul'         => $judul,
                'link'          => trim((string) $item->link),
                'sumber'        => $sumber ?: 'Tidak diketahui',
                'domain'        => $urlSumber !== '' ? (parse_url($urlSumber, PHP_URL_HOST) ?: '') : '',
                'tanggal'       => $tanggal->toDateTimeString(),
                'tanggal_label' => $tanggal->locale('id')->translatedFormat('j F Y'),
            ];
        }

        return $hasil;
    }
}
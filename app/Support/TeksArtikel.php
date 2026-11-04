<?php
// <!-- File ini berfungsi untuk membersihkan teks artikel dari elemen-elemen yang tidak relevan, seperti banner paywall dan promosi -->

namespace App\Support;

class TeksArtikel
{
    /** kalimat umum yang sering muncul pada konten berita berbayar. */
    private const POLA_NOISE = [
        'berlangganan', 'langganan', 'subscribe',
        'konten premium', 'akses premium', 'single data premium',
        'metode pembayaran', 'beli secara satuan',
        'langsung baca', 'login untuk', 'masuk untuk', 'daftar untuk', 'daftar sekarang',
        'baca juga', 'baca selengkapnya',
        'konten eksklusif', 'fitur eksklusif', 'khusus pelanggan', 'hanya untuk pelanggan',
        'unduh aplikasi', 'ikuti kami', 'bergabung dengan channel',
    ];

    /** Paragraf di bawah batas ini dan memuat frasa noise dianggap bukan isi berita. */
    private const BATAS_PANJANG_NOISE = 300;

    /**
     * Bersihkan paragraf hasil ekstraksi dari banner paywall dan promosi.
     *
     * @param  array<int,string>  $paragraf
     * @return array{paragraf: array<int,string>, dibuang: int, lengkap: bool}
     */
    public static function bersihkan(array $paragraf): array
    {
        $bersih = [];

        foreach ($paragraf as $p) {
            if (mb_strlen($p) < self::BATAS_PANJANG_NOISE && self::mengandungNoise($p)) {
                continue;
            }

            $bersih[] = $p;
        }

        $totalKarakter = array_sum(array_map('mb_strlen', $bersih));

        return [
            'paragraf' => $bersih,
            'dibuang'  => count($paragraf) - count($bersih),
            // Berita normal biasanya punya beberapa paragraf dan lebih dari 600 karakter
            'lengkap'  => count($bersih) >= 3 && $totalKarakter >= 600,
        ];
    }

    private static function mengandungNoise(string $teks): bool
    {
        foreach (self::POLA_NOISE as $pola) {
            if (mb_stripos($teks, $pola) !== false) {
                return true;
            }
        }

        return false;
    }
}
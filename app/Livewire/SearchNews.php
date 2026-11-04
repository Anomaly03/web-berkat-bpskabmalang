<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use App\Services\AIService;
use App\Services\ArticleExtractorService;
use App\Services\NewsService;
use App\Support\TeksArtikel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SearchNews extends Component
{
    // ---- Form ----
    public string $sektorId = '';
    public string $subsektorId = '';
    public string $subSubsektorId = '';
    public string $bulan = '';
    public string $tahun = '';

    // ---- Hasil pencarian ----
    #[Locked]
    public array $hasil = [];
    public bool $sudahCari = false;
    public ?string $pesanError = null;
    public string $labelPencarian = '';

    // ---- Konteks pencarian (dikunci saat tombol Cari ditekan) ----
    #[Locked]
    public array $jalurPencarian = [];

    #[Locked]
    public ?int $kategoriIdHasil = null;

    #[Locked]
    public int $bulanHasil = 0;

    #[Locked]
    public int $tahunHasil = 0;

    #[Locked]
    public string $periodeLabel = '';

    // ---- Modal artikel (baca + review berdampingan) ----
    public bool $modalBuka = false;

    #[Locked]
    public ?array $artikelDipilih = null;

    #[Locked]
    public ?array $bacaan = null;

    #[Locked]
    public ?string $peringatanBacaan = null;

    public ?string $pesanBacaan = null;
    public string $teksManual = '';

    // ---- Panel review ringkasan (di samping artikel) ----
    public bool $modalReview = false;
    public string $ringkasan = '';
    public ?string $pesanRingkas = null;

    /** Artikel yang sudah tersimpan untuk kategori + bulan yang sama (akan diganti saat simpan). */
    #[Locked]
    public ?array $artikelLama = null;

    // ---- Pemberitahuan setelah simpan ----
    public ?string $notifikasi = null;

    public function updatedSektorId(): void
    {
        $this->subsektorId = '';
        $this->subSubsektorId = '';
    }

    public function updatedSubsektorId(): void
    {
        $this->subSubsektorId = '';
    }

    private function kategoriTerpilih(): ?Category
    {
        $id = $this->subSubsektorId ?: $this->subsektorId ?: $this->sektorId;

        return $id !== '' ? Category::find((int) $id) : null;
    }

    /** Jalur dari sektor sampai level terdalam. */
    private function jalurKategori(?Category $kategori): array
    {
        $jalur = [];

        while ($kategori) {
            array_unshift($jalur, $kategori->nama_kategori);
            $kategori = $kategori->parent;
        }

        return $jalur;
    }

    public function search(NewsService $news): void
    {
        $this->validate([
            'sektorId' => 'required',
            'bulan'    => 'required',
            'tahun'    => 'required',
        ], [
            'sektorId.required' => 'Pilih sektor terlebih dahulu.',
            'bulan.required'    => 'Pilih bulan.',
            'tahun.required'    => 'Pilih tahun.',
        ]);

        $kategori = $this->kategoriTerpilih();

        if ($kategori === null) {
            $this->addError('sektorId', 'Kategori tidak ditemukan.');

            return;
        }

        $this->tutup();
        $this->notifikasi = null;
        $this->pesanError = null;
        $this->hasil = [];
        $this->sudahCari = true;

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $this->periodeLabel = $namaBulan[(int) $this->bulan] . ' ' . $this->tahun;
        $this->labelPencarian = $kategori->nama_kategori . ' · ' . $this->periodeLabel;
        $this->jalurPencarian = $this->jalurKategori($kategori);
        $this->kategoriIdHasil = $kategori->id;
        $this->bulanHasil = (int) $this->bulan;
        $this->tahunHasil = (int) $this->tahun;

        try {
            $this->hasil = $news->search($kategori->nama_kategori, (int) $this->bulan, (int) $this->tahun);
        } catch (\Throwable $e) {
            $this->pesanError = $e->getMessage();
        }
    }

    /** Klik "Pilih artikel ini": ambil isi artikel, bersihkan, lalu buka modal. */
    public function pilih(int $index, ArticleExtractorService $extractor): void
    {
        $artikel = $this->hasil[$index] ?? null;

        if ($artikel === null) {
            return;
        }

        $this->notifikasi = null;
        $this->artikelDipilih = $artikel;
        $this->bacaan = null;
        $this->pesanBacaan = null;
        $this->peringatanBacaan = null;
        $this->pesanRingkas = null;
        $this->teksManual = '';
        $this->ringkasan = '';
        $this->artikelLama = null;
        $this->modalReview = false;
        $this->resetErrorBag();

        try {
            $hasilEkstrak = $extractor->ekstrak($artikel['link']);
            $bersih = TeksArtikel::bersihkan($hasilEkstrak['paragraf']);

            if (count($bersih['paragraf']) < 2) {
                throw new \RuntimeException('Artikel ini tampaknya berbayar (paywall): hanya cuplikan yang dapat dibaca.');
            }

            $hasilEkstrak['paragraf'] = $bersih['paragraf'];
            $this->bacaan = $hasilEkstrak;

            if (! $bersih['lengkap']) {
                $this->peringatanBacaan = 'Isi artikel tampak tidak lengkap (kemungkinan sebagian berbayar atau terpotong). '
                    . 'Periksa lewat "Buka Tautan Asli" sebelum meringkas.';
            }
        } catch (\RuntimeException $e) {
            $this->pesanBacaan = $e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Ekstraksi artikel gagal', ['link' => $artikel['link'], 'error' => $e->getMessage()]);
            $this->pesanBacaan = 'Terjadi kesalahan saat membaca artikel.';
        }

        $this->modalBuka = true;
    }

    /** Jalur cadangan: pengguna menempel isi artikel sendiri. */
    public function pakaiTeksManual(): void
    {
        $this->validate(
            ['teksManual' => 'required|min:200'],
            [
                'teksManual.required' => 'Tempel isi artikel terlebih dahulu.',
                'teksManual.min'      => 'Teks terlalu pendek (minimal 200 karakter).',
            ]
        );

        $paragraf = array_values(array_filter(array_map('trim', preg_split('/\R+/u', $this->teksManual))));

        $this->bacaan = [
            'url_asli' => $this->artikelDipilih['link'],
            'domain'   => $this->artikelDipilih['domain'] ?: $this->artikelDipilih['sumber'],
            'penulis'  => null,
            'paragraf' => $paragraf,
        ];
        $this->pesanBacaan = null;
        $this->peringatanBacaan = null;
    }

    /** Klik "Pilih & Ringkas": minta AI meringkas, lalu tampilkan panel review di samping artikel. */
    public function ringkas(AIService $ai): void
    {
        if ($this->bacaan === null || $this->artikelDipilih === null) {
            return;
        }

        $this->pesanRingkas = null;

        try {
            $this->ringkasan = $ai->ringkas(
                $this->artikelDipilih['judul'],
                $this->bacaan['paragraf'],
                Arr::last($this->jalurPencarian) ?? ''
            );

            // Beri tahu user jika kategori + bulan ini sudah punya artikel tersimpan
            $lama = Article::where('category_id', $this->kategoriIdHasil)
                ->where('bulan_target', $this->bulanHasil)
                ->where('tahun_target', $this->tahunHasil)
                ->first();

            $this->artikelLama = $lama ? [
                'judul'   => $lama->judul,
                'sumber'  => $lama->sumber,
                'tanggal' => $lama->tanggal_rilis->locale('id')->translatedFormat('j F Y'),
            ] : null;

            $this->resetErrorBag('ringkasan');
            $this->modalReview = true; // artikel tetap terbuka di kiri
        } catch (\RuntimeException $e) {
            $this->pesanRingkas = $e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Peringkasan gagal', ['error' => $e->getMessage()]);
            $this->pesanRingkas = 'Terjadi kesalahan saat meringkas artikel.';
        }
    }

    /** Klik "Batal": tutup panel ringkasan saja, artikel tetap bisa dibaca. */
    public function batalReview(): void
    {
        $this->modalReview = false;
        $this->ringkasan = '';
        $this->pesanRingkas = null;
        $this->artikelLama = null;
        $this->resetErrorBag('ringkasan');
    }

    /** Klik "Simpan ke Data" / "Ganti & Simpan". */
    public function simpan(): void
    {
        $this->validate(
            ['ringkasan' => 'required|min:50|max:3000'],
            [
                'ringkasan.required' => 'Ringkasan tidak boleh kosong.',
                'ringkasan.min'      => 'Ringkasan terlalu pendek (minimal 50 karakter).',
                'ringkasan.max'      => 'Ringkasan terlalu panjang (maksimal 3000 karakter).',
            ]
        );

        if ($this->kategoriIdHasil === null || $this->artikelDipilih === null || $this->bacaan === null) {
            return;
        }

        try {
            // Kunci unik: kategori + bulan + tahun. Jika sudah ada, isinya diganti.
            $artikel = Article::updateOrCreate(
                [
                    'category_id'  => $this->kategoriIdHasil,
                    'bulan_target' => $this->bulanHasil,
                    'tahun_target' => $this->tahunHasil,
                ],
                [
                    'judul'         => mb_substr($this->artikelDipilih['judul'], 0, 255),
                    'link'          => $this->bacaan['url_asli'],
                    'sumber'        => mb_substr($this->artikelDipilih['sumber'], 0, 255),
                    'tanggal_rilis' => substr($this->artikelDipilih['tanggal'], 0, 10),
                    'ringkasan'     => trim($this->ringkasan),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan artikel', ['error' => $e->getMessage()]);
            $this->pesanRingkas = 'Gagal menyimpan ke database. Coba lagi.';

            return;
        }

        $pesan = $artikel->wasRecentlyCreated
            ? 'Ringkasan berhasil disimpan ke data.'
            : 'Artikel sebelumnya berhasil diganti dengan yang baru.';

        $this->tutup();
        $this->notifikasi = $pesan;
    }

    public function tutup(): void
    {
        $this->reset([
            'modalBuka', 'modalReview', 'artikelDipilih', 'bacaan', 'peringatanBacaan',
            'pesanBacaan', 'pesanRingkas', 'teksManual', 'ringkasan', 'artikelLama',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        $byParent = fn ($id) => $id !== ''
            ? Category::where('parent_id', $id)->orderBy('id')->get()
            : collect();

        // Data tersimpan untuk bulan yang sedang dicari (semua kategori)
        $tersimpan = $this->sudahCari && $this->bulanHasil > 0
            ? Article::with('category')
                ->where('bulan_target', $this->bulanHasil)
                ->where('tahun_target', $this->tahunHasil)
                ->orderBy('category_id')
                ->get()
            : collect();

        return view('livewire.search-news', [
            'sektorOptions'       => Category::whereNull('parent_id')->orderBy('id')->get(),
            'subsektorOptions'    => $byParent($this->sektorId),
            'subSubsektorOptions' => $byParent($this->subsektorId),
            'tersimpan'           => $tersimpan,
            'totalKategori'       => Category::count(),
        ]);
    }
}
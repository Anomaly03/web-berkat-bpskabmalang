<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\ArticleExtractorService;
use App\Services\NewsService;
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

    #[Locked]
    public array $jalurPencarian = [];

    // ---- Modal baca artikel ----
    public bool $modalBuka = false;

    #[Locked]
    public ?array $artikelDipilih = null;

    #[Locked]
    public ?array $bacaan = null;

    public ?string $pesanBacaan = null;
    public string $teksManual = '';

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

    /** Jalur dari sektor sampai level terdalam, mis. ["C. Industri Pengolahan", "2. Industri Makanan dan Minuman"] */
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
        $this->pesanError = null;
        $this->hasil = [];
        $this->sudahCari = true;

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $this->labelPencarian = $kategori->nama_kategori . ' · ' . $namaBulan[(int) $this->bulan] . ' ' . $this->tahun;
        $this->jalurPencarian = $this->jalurKategori($kategori);

        try {
            $this->hasil = $news->search($kategori->nama_kategori, (int) $this->bulan, (int) $this->tahun);
        } catch (\Throwable $e) {
            $this->pesanError = $e->getMessage();
        }
    }

    /** Klik "Pilih artikel ini": ambil isi artikel, lalu buka modal baca. */
    public function pilih(int $index, ArticleExtractorService $extractor): void
    {
        $artikel = $this->hasil[$index] ?? null;

        if ($artikel === null) {
            return;
        }

        $this->artikelDipilih = $artikel;
        $this->bacaan = null;
        $this->pesanBacaan = null;
        $this->teksManual = '';
        $this->resetErrorBag('teksManual');

        try {
            $this->bacaan = $extractor->ekstrak($artikel['link']);
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
    }

    public function ringkas(): void
    {
        // SEMENTARA: tahap berikutnya -> AIService::summarize(), lalu tampilkan review & simpan.
        dd($this->artikelDipilih, $this->bacaan);
    }

    public function tutup(): void
    {
        $this->reset(['modalBuka', 'artikelDipilih', 'bacaan', 'pesanBacaan', 'teksManual']);
    }

    public function render()
    {
        $byParent = fn ($id) => $id !== ''
            ? Category::where('parent_id', $id)->orderBy('id')->get()
            : collect();

        return view('livewire.search-news', [
            'sektorOptions'       => Category::whereNull('parent_id')->orderBy('id')->get(),
            'subsektorOptions'    => $byParent($this->sektorId),
            'subSubsektorOptions' => $byParent($this->subsektorId),
        ]);
    }
}
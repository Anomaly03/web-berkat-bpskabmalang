<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\NewsService;
use Livewire\Component;

class SearchNews extends Component
{
    public string $sektorId = '';
    public string $subsektorId = '';
    public string $subSubsektorId = '';
    public string $bulan = '';
    public string $tahun = '';

    public array $hasil = [];
    public bool $sudahCari = false;
    public ?string $pesanError = null;
    public string $labelPencarian = '';

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

        $this->pesanError = null;
        $this->hasil = [];
        $this->sudahCari = true;

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $this->labelPencarian = $kategori->nama_kategori . ' · ' . $namaBulan[(int) $this->bulan] . ' ' . $this->tahun;

        try {
            $this->hasil = $news->search($kategori->nama_kategori, (int) $this->bulan, (int) $this->tahun);
        } catch (\Throwable $e) {
            $this->pesanError = $e->getMessage();
        }
    }

    public function pilih(int $index): void
    {
        // SEMENTARA: cek artikel yang dipilih. Nanti diganti ekstraksi + ringkasan AI.
        dd($this->hasil[$index] ?? null);
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
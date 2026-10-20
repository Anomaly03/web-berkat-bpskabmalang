<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class SearchNews extends Component
{
    public string $sektorId = '';
    public string $subsektorId = '';
    public string $subSubsektorId = '';
    public string $bulan = '';
    public string $tahun = '';

    // Ganti pilihan induk -> reset semua pilihan di bawahnya
    public function updatedSektorId(): void
    {
        $this->subsektorId = '';
        $this->subSubsektorId = '';
    }

    public function updatedSubsektorId(): void
    {
        $this->subSubsektorId = '';
    }

    // Kategori final = level terdalam yang dipilih user
    private function kategoriTerpilih(): ?int
    {
        $id = $this->subSubsektorId ?: $this->subsektorId ?: $this->sektorId;

        return $id !== '' ? (int) $id : null;
    }

    public function search(): void
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

        // SEMENTARA: cek data yang terkirim. Nanti diganti pemanggilan NewsService.
        dd($this->kategoriTerpilih(), $this->bulan, $this->tahun);
    }

    public function render()
    {
        $byParent = fn ($id) => $id !== ''
            ? Category::where('parent_id', $id)->orderBy('id')->get()
            : collect();

        return view('livewire.search-news', [
            'sektorOptions'      => Category::whereNull('parent_id')->orderBy('id')->get(),
            'subsektorOptions'   => $byParent($this->sektorId),
            'subSubsektorOptions' => $byParent($this->subsektorId),
        ]);
    }
}
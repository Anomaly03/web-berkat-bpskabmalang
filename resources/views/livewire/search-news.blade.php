@php
    $bulanList = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $tahunList = range(now()->year, now()->year - 5);

    $selectClass = 'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 '
        . 'focus:border-bps focus:outline-none focus:ring-1 focus:ring-bps '
        . 'disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400';
    $labelClass = 'mb-1 block text-[11px] font-medium uppercase tracking-wide text-slate-500';
    $errorClass = 'mt-1 text-xs text-red-600';

    $gridCols = $subSubsektorOptions->isNotEmpty() ? 'lg:grid-cols-5' : 'lg:grid-cols-4';
@endphp

<div>
    {{-- ===== FORM PENCARIAN ===== --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form wire:submit="search">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $gridCols }}">

                <div>
                    <label for="sektor" class="{{ $labelClass }}">Sektor</label>
                    <select id="sektor" wire:model.live="sektorId" class="{{ $selectClass }}">
                        <option value="">Pilih</option>
                        @foreach ($sektorOptions as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_kategori }}</option>
                        @endforeach
                    </select>
                    @error('sektorId') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subsektor" class="{{ $labelClass }}">Subsektor</label>
                    <select id="subsektor" wire:model.live="subsektorId"
                            class="{{ $selectClass }}"
                            @disabled($subsektorOptions->isEmpty())>
                        <option value="">Pilih</option>
                        @foreach ($subsektorOptions as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($subSubsektorOptions->isNotEmpty())
                    <div>
                        <label for="subsub" class="{{ $labelClass }}">Sub-subsektor</label>
                        <select id="subsub" wire:model.live="subSubsektorId" class="{{ $selectClass }}">
                            <option value="">Pilih</option>
                            @foreach ($subSubsektorOptions as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="bulan" class="{{ $labelClass }}">Bulan</label>
                    <select id="bulan" wire:model="bulan" class="{{ $selectClass }}">
                        <option value="">Pilih</option>
                        @foreach ($bulanList as $angka => $nama)
                            <option value="{{ $angka }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('bulan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tahun" class="{{ $labelClass }}">Tahun</label>
                    <select id="tahun" wire:model="tahun" class="{{ $selectClass }}">
                        <option value="">Pilih</option>
                        @foreach ($tahunList as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </select>
                    @error('tahun') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="search"
                    class="mt-5 flex w-full items-center justify-center gap-2 rounded-md bg-bps px-4 py-2.5 text-sm font-medium text-white transition hover:bg-bps-dark focus:outline-none focus:ring-2 focus:ring-bps focus:ring-offset-2 disabled:opacity-60">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                </svg>
                <span wire:loading.remove wire:target="search">Cari Berita</span>
                <span wire:loading wire:target="search">Mencari...</span>
            </button>
        </form>
    </section>

    {{-- ===== HASIL PENCARIAN ===== --}}
    @if ($sudahCari)
        <section class="mt-6">
            <div class="mb-2 flex items-center justify-between px-1 text-xs text-slate-500">
                <p>Menampilkan hasil untuk <span class="font-semibold text-slate-800">{{ $labelPencarian }}</span></p>
                @if (! $pesanError)
                    <p class="text-slate-400">{{ count($hasil) }} artikel ditemukan</p>
                @endif
            </div>

            @if ($pesanError)
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $pesanError }}
                </div>
            @elseif (count($hasil) === 0)
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                    Tidak ada artikel yang ditemukan untuk kombinasi ini. Coba sektor atau bulan lain.
                </div>
            @else
                <div class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white shadow-sm">
                    @foreach ($hasil as $i => $artikel)
                        <article wire:key="artikel-{{ $i }}" class="px-5 py-4">
                            <p class="text-[13px] text-slate-700">{{ $artikel['sumber'] }}</p>
                            @if ($artikel['domain'])
                                <p class="mb-1 text-xs text-slate-400">https://{{ $artikel['domain'] }}</p>
                            @endif

                            <a href="{{ $artikel['link'] }}" target="_blank" rel="noopener noreferrer"
                               class="text-base font-medium text-blue-700 hover:underline">
                                {{ $artikel['judul'] }}
                            </a>

                            <p class="mt-1 text-xs text-slate-400">{{ $artikel['tanggal_label'] }}</p>

                            <button type="button" wire:click="pilih({{ $i }})"
                                    wire:loading.attr="disabled" wire:target="pilih"
                                    class="mt-3 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-bps hover:text-bps disabled:opacity-60">
                                Pilih artikel ini
                            </button>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- ===== OVERLAY LOADING (ambil artikel / ringkas AI) ===== --}}
    <div wire:loading.flex wire:target="pilih,ringkas"
         class="fixed inset-0 z-[60] items-center justify-center bg-slate-900/40 backdrop-blur-sm">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-xl">
            <svg class="h-5 w-5 animate-spin text-bps" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
            </svg>
            <p class="text-sm text-slate-700">
                <span wire:loading wire:target="pilih">Mengambil isi artikel...</span>
                <span wire:loading wire:target="ringkas">Meringkas dengan AI...</span>
            </p>
        </div>
    </div>

    {{-- ===== MODAL ARTIKEL (baca + review ringkasan berdampingan) ===== --}}
    @if ($modalBuka && $artikelDipilih)
        <x-reader-modal :artikel="$artikelDipilih"
                        :bacaan="$bacaan"
                        :pesan="$pesanBacaan"
                        :jalur="$jalurPencarian"
                        :error-ringkas="$pesanRingkas"
                        :peringatan="$peringatanBacaan"
                        :review="$modalReview"
                        :periode="$periodeLabel"
                        :awal="$ringkasan" />
    @endif
</div>
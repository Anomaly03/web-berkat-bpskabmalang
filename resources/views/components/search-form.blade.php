@props(['sectors' => collect()])

@php
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $tahun = range(now()->year, now()->year - 5);

    $selectClass = 'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 '
        . 'focus:border-bps focus:outline-none focus:ring-1 focus:ring-bps '
        . 'disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400';
    $labelClass = 'mb-1 block text-[11px] font-medium uppercase tracking-wide text-slate-500';
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <form>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <label for="sektor" class="{{ $labelClass }}">Sektor</label>
                <select id="sektor" name="sektor" class="{{ $selectClass }}">
                    <option value="">Pilih</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}">{{ $sector->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subsektor" class="{{ $labelClass }}">Subsektor</label>
                <select id="subsektor" name="subsektor" class="{{ $selectClass }}" disabled>
                    <option value="">Pilih</option>
                </select>
            </div>

            <div>
                <label for="bulan" class="{{ $labelClass }}">Bulan</label>
                <select id="bulan" name="bulan" class="{{ $selectClass }}">
                    <option value="">Pilih</option>
                    @foreach ($bulan as $angka => $nama)
                        <option value="{{ $angka }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tahun" class="{{ $labelClass }}">Tahun</label>
                <select id="tahun" name="tahun" class="{{ $selectClass }}">
                    <option value="">Pilih</option>
                    @foreach ($tahun as $th)
                        <option value="{{ $th }}">{{ $th }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <button type="button"
                class="mt-5 flex w-full items-center justify-center gap-2 rounded-md bg-bps px-4 py-2.5 text-sm font-medium text-white transition hover:bg-bps-dark focus:outline-none focus:ring-2 focus:ring-bps focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
            </svg>
            Cari Berita
        </button>
    </form>
</section>
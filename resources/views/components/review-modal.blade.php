@props(['artikel', 'jalur' => [], 'periode' => '', 'awal' => ''])

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-data="{ n: {{ mb_strlen($awal) }} }"
     x-on:keydown.escape.window="$wire.tutup()">

    {{-- Latar belakang (klik untuk menutup) --}}
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="tutup"></div>

    <div class="relative w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200"
         role="dialog" aria-modal="true" aria-labelledby="judul-review">

        <div class="h-1 bg-indigo-300"></div>

        {{-- ===== Header ===== --}}
        <div class="px-6 pb-4 pt-5">
            <div class="flex items-start justify-between">
                <span class="inline-block rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-800">
                    Hasil Analisis AI
                </span>
                <button type="button" wire:click="tutup" aria-label="Tutup"
                        class="rounded p-1 text-slate-500 transition hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <h2 id="judul-review" class="mt-2 text-xl font-bold text-slate-900">Review Artikel</h2>
            <p class="mt-1 text-xs text-slate-500">
                {{ \Illuminate\Support\Arr::last($jalur) }} &rsaquo; {{ $periode }}
            </p>
            <p class="mt-2 truncate text-xs text-slate-400" title="{{ $artikel['judul'] }}">{{ $artikel['judul'] }}</p>
        </div>

        <hr class="border-slate-200">

        {{-- ===== Ringkasan ===== --}}
        <div class="px-6 py-4">
            <div class="mb-2 flex items-center justify-between">
                <label for="ringkasan" class="text-sm font-semibold text-slate-800">Ringkasan</label>
                <span class="text-[11px] font-medium text-slate-500"><span x-text="n"></span> karakter</span>
            </div>

            <textarea id="ringkasan" rows="7" wire:model="ringkasan"
                      x-on:input="n = $event.target.value.length"
                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm leading-relaxed text-slate-800 focus:border-bps focus:outline-none focus:ring-1 focus:ring-bps"></textarea>

            @error('ringkasan')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <p class="mt-3 flex items-start gap-1.5 text-[11px] italic text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <span>Anda dapat menyunting ringkasan di atas sebelum data dimasukkan ke dalam arsip monitoring bulanan BPS.</span>
            </p>
        </div>

        {{-- ===== Footer ===== --}}
        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-100 px-6 py-3">
            <button type="button" wire:click="tutup"
                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                Batal
            </button>

            <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                    class="flex items-center gap-2 rounded-md bg-bps px-4 py-2 text-sm font-medium text-white transition hover:bg-bps-dark disabled:opacity-60">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                Simpan ke Data
            </button>
        </div>
    </div>
</div>
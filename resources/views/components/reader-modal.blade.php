@props([
    'artikel',
    'bacaan' => null,
    'pesan' => null,
    'jalur' => [],
    'errorRingkas' => null,
    'peringatan' => null,
    'review' => false,
    'periode' => '',
    'awal' => '',
])

@php
    $waktu   = \Carbon\Carbon::parse($artikel['tanggal'])->locale('id')->translatedFormat('j F Y, H:i') . ' WIB';
    $urlAsli = $bacaan['url_asli'] ?? $artikel['link'];
    $domain  = $bacaan['domain'] ?? ($artikel['domain'] ?: $artikel['sumber']);
    $tombolKecil = 'flex h-7 min-w-7 items-center justify-center rounded px-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200';
@endphp

<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-data="{ size: 16 }"
     x-on:keydown.escape.window="$wire.tutup()">

    {{-- Latar belakang (klik untuk menutup) --}}
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="tutup"></div>

    <div class="relative flex w-full flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 {{ $review ? 'h-[90vh] max-w-6xl' : 'max-h-[90vh] max-w-3xl' }}"
         role="dialog" aria-modal="true" aria-labelledby="judul-bacaan">

        {{-- ===== Header: breadcrumb, ukuran huruf, tutup ===== --}}
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-100 px-5 py-3">
            <p class="min-w-0 truncate rounded bg-slate-200/80 px-2 py-1 text-[11px] text-slate-600">
                {{ implode(' / ', $jalur) }}
            </p>

            <div class="flex shrink-0 items-center gap-1">
                <button type="button" class="{{ $tombolKecil }}" aria-label="Perkecil huruf"
                        x-on:click="size = Math.max(14, size - 1)">A-</button>
                <button type="button" class="{{ $tombolKecil }}" aria-label="Perbesar huruf"
                        x-on:click="size = Math.min(24, size + 1)">A+</button>
                <button type="button" class="{{ $tombolKecil }}" aria-label="Tutup" wire:click="tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ===== Badan: artikel (kiri) + ringkasan (kanan, setelah diringkas) ===== --}}
        <div class="flex min-h-0 flex-1 flex-col lg:flex-row">

            {{-- ----- Kolom artikel ----- --}}
            <section class="min-h-0 flex-1 overflow-y-auto px-8 py-6 {{ $review ? 'lg:border-r lg:border-slate-200' : '' }}">
                <h2 id="judul-bacaan" class="text-2xl font-bold leading-snug text-slate-900">
                    {{ $artikel['judul'] }}
                </h2>

                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span class="font-semibold text-slate-800">{{ $artikel['sumber'] }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span>{{ $waktu }}</span>
                    @if (! empty($bacaan['penulis']))
                        <span aria-hidden="true">&middot;</span>
                        <span>{{ $bacaan['penulis'] }}</span>
                    @endif
                    <span aria-hidden="true">&middot;</span>
                    <a href="{{ $urlAsli }}" target="_blank" rel="noopener noreferrer"
                       class="font-medium text-bps hover:underline">Buka Tautan Asli &#8599;</a>
                </div>

                <hr class="my-5 border-slate-200">

                @if ($peringatan)
                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                        {{ $peringatan }}
                    </div>
                @endif

                @if ($bacaan)
                    <div class="space-y-4 leading-relaxed text-slate-800" x-bind:style="'font-size:' + size + 'px'">
                        @foreach ($bacaan['paragraf'] as $p)
                            <p>{{ $p }}</p>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <p class="font-medium">Isi artikel tidak dapat dimuat otomatis.</p>
                        <p class="mt-1">{{ $pesan }}</p>
                    </div>

                    <div class="mt-5">
                        <label for="teks-manual" class="mb-1 block text-xs font-medium text-slate-600">
                            Atau tempel isi artikel secara manual (buka tautan asli, salin teksnya)
                        </label>
                        <textarea id="teks-manual" wire:model="teksManual" rows="8"
                                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-bps focus:outline-none focus:ring-1 focus:ring-bps"></textarea>
                        @error('teksManual')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <button type="button" wire:click="pakaiTeksManual"
                                class="mt-3 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-bps hover:text-bps">
                            Gunakan teks ini
                        </button>
                    </div>
                @endif
            </section>

            {{-- ----- Kolom ringkasan ----- --}}
            @if ($review)
                <aside class="flex min-h-0 flex-1 flex-col overflow-y-auto border-t border-slate-200 bg-slate-50 px-6 py-6 lg:border-t-0"
                       x-data="{ n: {{ mb_strlen($awal) }} }">

                    <span class="inline-block w-fit rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-800">
                        Hasil Analisis AI
                    </span>
                    <h3 class="mt-2 text-xl font-bold text-slate-900">Review Ringkasan</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ \Illuminate\Support\Arr::last($jalur) }} &rsaquo; {{ $periode }}
                    </p>

                    <div class="mb-2 mt-5 flex items-center justify-between">
                        <label for="ringkasan" class="text-sm font-semibold text-slate-800">Ringkasan</label>
                        <span class="text-[11px] font-medium text-slate-500"><span x-text="n"></span> karakter</span>
                    </div>

                    <textarea id="ringkasan" wire:model="ringkasan"
                              x-on:input="n = $event.target.value.length"
                              class="min-h-48 w-full flex-1 resize-none rounded-md border border-slate-300 bg-white px-3 py-2 text-sm leading-relaxed text-slate-800 focus:border-bps focus:outline-none focus:ring-1 focus:ring-bps"></textarea>

                    @error('ringkasan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-3 flex items-start gap-1.5 text-[11px] italic text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <span>Cocokkan angka dan nama dengan artikel di sebelah kiri, lalu sunting sebelum data dimasukkan ke arsip monitoring bulanan BPS.</span>
                    </p>
                </aside>
            @endif
        </div>

        {{-- ===== Pesan gagal meringkas ===== --}}
        @if ($errorRingkas)
            <div class="border-t border-red-200 bg-red-50 px-5 py-2 text-xs text-red-700">
                {{ $errorRingkas }}
            </div>
        @endif

        {{-- ===== Footer: domain + aksi ===== --}}
        <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-100 px-5 py-3">
            <p class="flex min-w-0 items-center gap-1.5 truncate text-[11px] text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span class="truncate">{{ $domain }}</span>
            </p>

            <div class="flex shrink-0 items-center gap-2">
                @if ($review)
                    <button type="button" wire:click="batalReview"
                            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                            class="flex items-center gap-2 rounded-md bg-bps px-4 py-2 text-sm font-medium text-white transition hover:bg-bps-dark disabled:opacity-60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Simpan ke Data
                    </button>
                @else
                    <button type="button" wire:click="tutup"
                            class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                        &larr; Tutup Bacaan
                    </button>
                    <button type="button" wire:click="ringkas" @disabled(! $bacaan)
                            class="rounded-md bg-bps px-3 py-1.5 text-xs font-medium text-white transition hover:bg-bps-dark disabled:cursor-not-allowed disabled:opacity-50">
                        Pilih &amp; Ringkas Artikel Ini
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@props(['items', 'total' => 0, 'periode' => ''])

<section class="mt-8">
    <div class="mb-2 flex items-center gap-2 px-1">
        <h3 class="text-sm font-semibold text-slate-800">Data Tersimpan {{ $periode }}</h3>
        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-medium text-slate-600">
            {{ $items->count() }} dari {{ $total }} kategori
        </span>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-lg border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-500">
            Belum ada ringkasan yang disimpan untuk {{ $periode }}.
        </div>
    @else
        <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white shadow-sm">
            @foreach ($items as $item)
                <li wire:key="tersimpan-{{ $item->id }}" class="flex gap-3 px-5 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-label="Tersimpan">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>

                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-slate-500">{{ $item->category->nama_kategori }}</p>
                        <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                           class="block truncate text-sm font-medium text-blue-700 hover:underline">
                            {{ $item->judul }}
                        </a>
                        <p class="text-xs text-slate-400">
                            {{ $item->sumber }} &middot; {{ $item->tanggal_rilis->locale('id')->translatedFormat('j F Y') }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
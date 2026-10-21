<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/60 backdrop-blur-md">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">

        {{-- Logo + Identitas --}}
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center  rounded-md text-[11px] font-bold text-white">
                <img src="{{ asset('images/logo-bps.png') }}" alt="Logo BPS" class="h-10 w-10 object-contain" />
            </div>
            <div class="h-8 w-px bg-slate-300"></div>
            <div class="leading-tight">
                <p class="text-sm font-semibold text-slate-900">Badan Pusat Statistik</p>
                <p class="text-[11px] text-slate-500">Kabupaten Malang</p>
            </div>
        </div>

        {{-- Nama Sistem --}}
        <div class="text-right leading-tight">
            <p class="text-lg font-extrabold text-slate-900">BERKAT</p>
            <p class="text-sm font-normal text-slate-800">Berita Ekonomi Rekapitulasi Terpadu</p>
        </div>

    </div>
</header>
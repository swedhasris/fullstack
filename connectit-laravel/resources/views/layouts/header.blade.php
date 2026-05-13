{{-- ── Top Header Bar ──────────────────────────────────────────────────────── --}}
<header class="h-16 border-b border-white/10 flex items-center justify-between px-6 bg-[#0a0a0a] shrink-0">

    {{-- Page Title --}}
    <div class="flex flex-col">
        <h1 class="text-base font-bold">@yield('page-title', 'Dashboard')</h1>
        <div class="flex items-center gap-2 text-[9px] text-gray-500 font-bold uppercase tracking-widest">
            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
            @yield('page-subtitle', 'Live Service Monitoring')
        </div>
    </div>

    {{-- Right Actions --}}
    <div class="flex items-center gap-3">

        {{-- Quick Create Ticket --}}
        <a href="{{ route('tickets.create') }}"
           class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-primary/90 transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span class="hidden sm:inline">New Ticket</span>
        </a>

        {{-- Notifications Bell --}}
        <button class="relative p-2 hover:bg-white/5 rounded-lg transition-colors" title="Notifications">
            <i data-lucide="bell" class="w-4 h-4 text-gray-400"></i>
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>

        {{-- User Avatar --}}
        <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center text-primary font-bold text-xs">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>
</header>

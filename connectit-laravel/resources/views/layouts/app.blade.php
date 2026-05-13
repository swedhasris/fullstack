<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ConnectIT ITSM') | {{ config('app.name') }}</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Tailwind CSS (CDN for simplicity; swap for Vite build in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#81B532',
                        'sn-dark': '#0B141A',
                        'sn-sidebar': '#151B26',
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; letter-spacing: -0.01em; }
        .bg-sn-sidebar { background: #151B26; }
        .bg-sn-card { background: #161615; border: 1px solid rgba(255,255,255,0.05); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .heatmap-cell { width: 32px; height: 32px; border-radius: 4px; }
        .heatmap-cell-0 { background: rgba(255,255,255,0.03); }
        .heatmap-cell-1-2 { background: rgba(129,181,50,0.2); }
        .heatmap-cell-3-5 { background: rgba(129,181,50,0.5); }
        .heatmap-cell-6plus { background: #81B532; }
        /* Loader */
        .page-loader { position: fixed; inset: 0; background: #0a0a0a; z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.3s; }
        .spinner { width: 40px; height: 40px; border: 3px solid rgba(129,181,50,0.2); border-top-color: #81B532; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        /* SLA timer pulse */
        .sla-breach { animation: pulse-red 1.5s ease-in-out infinite; }
        @keyframes pulse-red { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
        /* Toast */
        .toast { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; }
    </style>

    @stack('styles')
</head>
<body class="h-full bg-[#0a0a0a] text-[#EDEDEC]"
      x-data="{ isSidebarCollapsed: false, showMobileMenu: false }">

    {{-- Page Loader --}}
    <div class="page-loader" id="pageLoader">
        <div class="text-center">
            <div class="spinner mx-auto mb-4"></div>
            <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Loading ConnectIT</div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">

        {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
        @include('layouts.sidebar')

        {{-- ── Main Content ─────────────────────────────────────────────────── --}}
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Header --}}
            @include('layouts.header')

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="toast" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="flex items-center gap-3 bg-green-900/90 border border-green-500/30 text-green-300 px-4 py-3 rounded-xl shadow-xl">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                    <button @click="show = false" class="ml-2 text-green-400 hover:text-white"><i data-lucide="x" class="w-3 h-3"></i></button>
                </div>
            </div>
            @endif

            @if(session('error') || $errors->any())
            <div class="toast" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="flex items-center gap-3 bg-red-900/90 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl shadow-xl max-w-sm">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span class="text-sm font-medium">
                        {{ session('error') ?? $errors->first() }}
                    </span>
                    <button @click="show = false" class="ml-2 text-red-400 hover:text-white"><i data-lucide="x" class="w-3 h-3"></i></button>
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-[#0a0a0a]">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- AI Chatbot --}}
    @include('layouts.chatbot')

    <script>
        // Hide loader after page load
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 300);
            }
        });

        // Initialize Lucide icons
        lucide.createIcons();

        // CSRF token for AJAX
        window.csrfToken = '{{ csrf_token() }}';

        // Axios-like fetch helper
        window.api = {
            async post(url, data) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                return res.json();
            },
            async patch(url, data) {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                return res.json();
            },
            async get(url) {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                return res.json();
            }
        };
    </script>

    @stack('scripts')
</body>
</html>

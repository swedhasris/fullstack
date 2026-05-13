{{-- ── Sidebar Navigation ──────────────────────────────────────────────────── --}}
<aside :class="isSidebarCollapsed ? 'w-16' : 'w-64'"
       class="bg-sn-sidebar text-white flex flex-col transition-all duration-300 border-r border-white/10 relative z-20 shrink-0">

    {{-- Logo --}}
    <div class="p-4 flex items-center justify-between border-b border-white/10 h-16">
        <div class="flex items-center gap-2 overflow-hidden" x-show="!isSidebarCollapsed" x-transition>
            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center font-bold text-primary text-sm">C</div>
            <span class="text-lg font-bold tracking-tight">ConnectIT</span>
        </div>
        <button @click="isSidebarCollapsed = !isSidebarCollapsed"
                class="p-1.5 hover:bg-white/10 rounded-lg transition-colors">
            <i data-lucide="menu" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-grow overflow-y-auto custom-scrollbar py-4 px-2 space-y-0.5">

        @php
            $user = auth()->user();
            $currentRoute = request()->route()?->getName() ?? '';
            $isActive = fn(string $route) => str_starts_with($currentRoute, $route) ? 'bg-primary/10 text-primary border border-primary/20' : 'text-gray-400 hover:bg-white/5 hover:text-white';
        @endphp

        {{-- Favorites --}}
        <div class="px-2 py-2 text-[9px] font-black uppercase tracking-widest text-gray-600" x-show="!isSidebarCollapsed">Favorites</div>

        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('dashboard') }}">
            <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Dashboard</span>
        </a>

        <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('tickets') }}">
            <i data-lucide="ticket" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Incidents</span>
        </a>

        {{-- ITSM --}}
        <div class="px-2 pt-4 pb-2 text-[9px] font-black uppercase tracking-widest text-gray-600" x-show="!isSidebarCollapsed">ITSM</div>

        @if($user->canManageTickets())
        <a href="{{ route('problems.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('problems') }}">
            <i data-lucide="bug" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Problem Mgmt</span>
        </a>

        <a href="{{ route('changes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('changes') }}">
            <i data-lucide="git-branch" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Change Mgmt</span>
        </a>
        @endif

        <a href="{{ route('assets.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('assets') }}">
            <i data-lucide="server" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">CMDB / Assets</span>
        </a>

        <a href="{{ route('knowledge.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('knowledge') }}">
            <i data-lucide="book-open" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Knowledge Base</span>
        </a>

        {{-- Analytics --}}
        @if($user->canManageTickets())
        <div class="px-2 pt-4 pb-2 text-[9px] font-black uppercase tracking-widest text-gray-600" x-show="!isSidebarCollapsed">Analytics</div>

        <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('reports') }}">
            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Reports</span>
        </a>

        @if($user->canManageSLA())
        <a href="{{ route('sla.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('sla') }}">
            <i data-lucide="clock" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">SLA Management</span>
        </a>
        @endif
        @endif

        {{-- Time Tracking --}}
        <div class="px-2 pt-4 pb-2 text-[9px] font-black uppercase tracking-widest text-gray-600" x-show="!isSidebarCollapsed">Time Tracking</div>

        <a href="{{ route('timesheets.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('timesheets') }}">
            <i data-lucide="timer" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Timesheets</span>
        </a>

        @if($user->canApproveTimesheets())
        <a href="{{ route('timesheets.approvals') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('timesheets.approvals') }}">
            <i data-lucide="check-square" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">TS Approvals</span>
        </a>
        @endif

        {{-- Administration --}}
        @if($user->canManageUsers())
        <div class="px-2 pt-4 pb-2 text-[9px] font-black uppercase tracking-widest text-gray-600" x-show="!isSidebarCollapsed">Administration</div>

        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('users') }}">
            <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">User Management</span>
        </a>
        @endif

        @if($user->role->canSystemSettings())
        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $isActive('settings') }}">
            <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
            <span class="text-sm font-medium truncate" x-show="!isSidebarCollapsed">Settings</span>
        </a>
        @endif

    </nav>

    {{-- User Profile Footer --}}
    <div class="p-3 border-t border-white/10">
        <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-white/5 transition-colors cursor-pointer"
             x-data="{ open: false }" @click="open = !open" x-ref="userMenu">

            <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center text-primary font-bold text-xs shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0" x-show="!isSidebarCollapsed">
                <div class="text-xs font-bold truncate">{{ auth()->user()->name }}</div>
                <div class="text-[9px] text-gray-500 uppercase font-bold">{{ auth()->user()->role->label() }}</div>
            </div>

            <i data-lucide="chevron-up" class="w-3 h-3 text-gray-500 shrink-0" x-show="!isSidebarCollapsed" :class="open ? '' : 'rotate-180'" style="transition: transform 0.2s"></i>

            {{-- Dropdown --}}
            <div x-show="open" @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="absolute bottom-16 left-3 right-3 bg-[#1a1a1a] border border-white/10 rounded-xl shadow-2xl overflow-hidden z-50">
                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i> Profile
                </a>
                <div class="border-t border-white/5"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:bg-red-500/10 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

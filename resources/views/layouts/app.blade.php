<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}{{ isset($title) ? ' — '.$title : '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..700;1,14..32,300..400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebar: true, mobileNav: false, notifOpen: false, userOpen: false }">

{{-- ===== SIDEBAR ===== --}}
{{-- Mobile overlay backdrop --}}
<div x-show="mobileNav" x-cloak @click="mobileNav=false"
     class="fixed inset-0 z-40 bg-black/50 md:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-gray-900 transition-all duration-300 md:translate-x-0"
    :class="{
        'w-64': sidebar,
        'w-16': !sidebar,
        '-translate-x-full': !mobileNav,
        'translate-x-0': mobileNav
    }">

    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-gray-700 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="shrink-0 w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
                </svg>
            </div>
            <span class="text-white font-bold text-lg tracking-tight truncate" x-show="sidebar" x-cloak>ChronoSphere</span>
        </div>
        <button @click="sidebar=!sidebar" class="ml-auto text-gray-400 hover:text-white shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path x-show="sidebar" stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                <path x-show="!sidebar" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-3 space-y-0.5 px-2">

        {{-- Dashboard --}}
        @php $isDash = request()->routeIs('dashboard'); @endphp
        <a href="{{ route('dashboard') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ $isDash ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
            </svg>
            <span x-show="sidebar" x-cloak>Tableau de bord</span>
        </a>

        <div class="my-3 border-t border-gray-700" x-show="sidebar" x-cloak></div>
        <p class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider" x-show="sidebar" x-cloak>Modules</p>

        {{-- Universitaire --}}
        @can('timetable.view')
        @php $isUni = request()->routeIs('timetable.*'); @endphp
        <a href="{{ route('timetable.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ $isUni ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/>
            </svg>
            <span x-show="sidebar" x-cloak>Universitaire</span>
        </a>
        @endcan

        {{-- Employés --}}
        @can('shifts.view')
        @php $isHr = request()->routeIs('shifts.*'); @endphp
        <a href="{{ route('shifts.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ $isHr ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="sidebar" x-cloak>Employés</span>
        </a>
        @endcan

        {{-- Agenda --}}
        @can('calendar.view')
        @php $isCal = request()->routeIs('calendar.*'); @endphp
        <a href="{{ route('calendar.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ $isCal ? 'bg-violet-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span x-show="sidebar" x-cloak>Agenda</span>
        </a>
        @endcan

        {{-- Réservation --}}
        @can('booking.view')
        @php $isBook = request()->routeIs('booking.*'); @endphp
        <a href="{{ route('booking.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ $isBook ? 'bg-orange-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="sidebar" x-cloak>Réservation</span>
        </a>
        @endcan

        {{-- Projet --}}
        @can('project.view')
        @php $isProj = request()->routeIs('project.*'); @endphp
        <a href="{{ route('project.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ $isProj ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            <span x-show="sidebar" x-cloak>Projet / Gantt</span>
        </a>
        @endcan

    </nav>

    {{-- User --}}
    <div class="border-t border-gray-700 p-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-semibold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0" x-show="sidebar" x-cloak>
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="sidebar" x-cloak>
                @csrf
                <button type="submit" class="text-gray-400 hover:text-white" title="Déconnexion">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ===== MAIN ===== --}}
<div class="transition-all duration-300" :class="sidebar ? 'md:pl-64' : 'md:pl-16'">

    {{-- Topbar --}}
    <header class="sticky top-0 z-40 bg-white border-b border-gray-200 h-14 flex items-center px-4 md:px-6 gap-4">
        {{-- Hamburger (mobile only) --}}
        <button @click="mobileNav=!mobileNav" class="md:hidden text-gray-500 hover:text-gray-700 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <h1 class="text-base font-semibold text-gray-900 truncate">
            {{ $title ?? 'Tableau de bord' }}
        </h1>

        <div class="ml-auto flex items-center gap-3">
            {{-- Flash success --}}
            @if(session('success'))
            <span class="text-sm text-emerald-600 font-medium">{{ session('success') }}</span>
            @endif

            {{-- Profile link --}}
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-semibold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
            </a>
        </div>
    </header>

    {{-- Flash errors --}}
    @if(session('error'))
    <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        {{ session('error') }}
    </div>
    @endif

    {{-- Content --}}
    <main class="p-4 md:p-6">
        {{ $slot }}
    </main>
</div>

@stack('scripts')
</body>
</html>

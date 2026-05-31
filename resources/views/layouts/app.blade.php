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
    <script>
        if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
    </script>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 font-sans antialiased"
      x-data="{
          sidebar: window.innerWidth >= 1024,
          mobileNav: false,
          darkMode: localStorage.getItem('darkMode') === 'true',
          toggleDark() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('darkMode', this.darkMode);
              document.documentElement.classList.toggle('dark', this.darkMode);
          }
      }">

{{-- ===== SIDEBAR ===== --}}
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
    <div class="flex items-center h-16 px-4 border-b border-gray-700/80 shrink-0">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <div class="shrink-0 w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
                </svg>
            </div>
            <span class="text-white font-bold text-lg tracking-tight truncate" x-show="sidebar" x-cloak>ChronoSphere</span>
        </div>
        <button @click="sidebar=!sidebar"
                class="hidden md:flex w-7 h-7 items-center justify-center rounded-lg text-gray-500 hover:text-white hover:bg-gray-700/60 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path x-show="sidebar" stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                <path x-show="!sidebar" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-3 space-y-0.5 px-2">

        @php $isDash = request()->routeIs('dashboard'); @endphp
        <a href="{{ route('dashboard') }}"
           title="Tableau de bord"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ $isDash ? 'bg-white/10 text-white shadow-sm' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ $isDash ? 'text-white' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
            </svg>
            <span x-show="sidebar" x-cloak class="truncate">Tableau de bord</span>
        </a>

        <div class="my-3 border-t border-gray-700/60" x-show="sidebar" x-cloak></div>
        <p class="px-3 py-1 text-[10px] font-bold text-gray-600 uppercase tracking-widest" x-show="sidebar" x-cloak>Modules</p>

        @can('timetable.view')
        @php $isUni = request()->routeIs('timetable.*'); @endphp
        <a href="{{ route('timetable.index') }}" title="Universitaire"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ $isUni ? 'bg-blue-600/90 text-white shadow-sm shadow-blue-500/20' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ $isUni ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/>
            </svg>
            <span x-show="sidebar" x-cloak class="truncate">Universitaire</span>
        </a>
        @endcan

        @can('shifts.view')
        @php $isHr = request()->routeIs('shifts.*'); @endphp
        <a href="{{ route('shifts.index') }}" title="Employés"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ $isHr ? 'bg-emerald-600/90 text-white shadow-sm shadow-emerald-500/20' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ $isHr ? 'text-white' : 'text-gray-500 group-hover:text-emerald-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="sidebar" x-cloak class="truncate">Employés</span>
        </a>
        @endcan

        @can('calendar.view')
        @php $isCal = request()->routeIs('calendar.*'); @endphp
        <a href="{{ route('calendar.index') }}" title="Agenda"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ $isCal ? 'bg-violet-600/90 text-white shadow-sm shadow-violet-500/20' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ $isCal ? 'text-white' : 'text-gray-500 group-hover:text-violet-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span x-show="sidebar" x-cloak class="truncate">Agenda</span>
        </a>
        @endcan

        @can('booking.view')
        @php $isBook = request()->routeIs('booking.*'); @endphp
        <a href="{{ route('booking.index') }}" title="Réservation"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ $isBook ? 'bg-orange-600/90 text-white shadow-sm shadow-orange-500/20' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ $isBook ? 'text-white' : 'text-gray-500 group-hover:text-orange-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="sidebar" x-cloak class="truncate">Réservation</span>
        </a>
        @endcan

        @can('project.view')
        @php $isProj = request()->routeIs('project.*'); @endphp
        <a href="{{ route('project.index') }}" title="Projet / Gantt"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ $isProj ? 'bg-indigo-600/90 text-white shadow-sm shadow-indigo-500/20' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0 {{ $isProj ? 'text-white' : 'text-gray-500 group-hover:text-indigo-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            <span x-show="sidebar" x-cloak class="truncate">Projet / Gantt</span>
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
    <header class="sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 h-14 flex items-center px-4 md:px-6 gap-4">
        <button @click="mobileNav=!mobileNav" class="md:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <h1 class="text-base font-semibold text-gray-900 dark:text-white truncate">
            {{ $title ?? 'Tableau de bord' }}
        </h1>

        <div class="ml-auto flex items-center gap-3">
            @if(session('success'))
            <span class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">{{ session('success') }}</span>
            @endif

            {{-- Notification bell --}}
            <div x-data="notifBell()" x-init="load()" class="relative">
                <button @click="toggle()"
                        class="relative w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"
                          class="absolute -top-1 -right-1 min-w-[16px] h-4 px-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none"></span>
                </button>

                <div x-show="open" x-cloak @click.outside="open=false"
                     class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</span>
                        <button @click="markAll()" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Tout marquer lu</button>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-if="notifications.length === 0">
                            <p class="px-4 py-6 text-sm text-center text-gray-400 dark:text-gray-500">Aucune notification</p>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <a :href="n.url || '#'" @click="markOne(n.id)"
                               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                               :class="n.read ? 'opacity-60' : ''">
                                <span class="mt-0.5 w-2 h-2 rounded-full shrink-0"
                                      :class="n.read ? 'bg-gray-300 dark:bg-gray-600' : 'bg-indigo-500'"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-snug" x-text="n.message"></p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5" x-text="n.created_at"></p>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Dark mode toggle --}}
            <button @click="toggleDark()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    :title="darkMode ? 'Mode clair' : 'Mode sombre'">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>

            {{-- Avatar dropdown : Profil + Déconnexion --}}
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open=false">
                <button @click="open=!open"
                        class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-lg px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-300 text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden sm:inline max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.outside="open=false"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">

                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Mon profil
                    </a>

                    <div class="border-t border-gray-100 dark:border-gray-700 mt-1 pt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @if(session('error'))
    <div class="mx-6 mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">
        {{ session('error') }}
    </div>
    @endif

    <main class="p-4 md:p-6">
        {{ $slot }}
    </main>
</div>

{{-- ===== TOASTS ===== --}}
<div x-data="{
        toasts: [],
        add(msg, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, msg, type });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
     }"
     @toast.window="add($event.detail.message, $event.detail.type ?? 'success')"
     class="fixed bottom-5 right-5 z-[200] space-y-2 pointer-events-none">
    <template x-for="t in toasts" :key="t.id">
        <div class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium
                    transition-all duration-300 min-w-[240px] max-w-xs"
             :class="{
                 'bg-white dark:bg-gray-800 border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300': t.type === 'success',
                 'bg-white dark:bg-gray-800 border-red-200 dark:border-red-700 text-red-700 dark:text-red-400': t.type === 'error',
                 'bg-white dark:bg-gray-800 border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-400': t.type === 'warning',
                 'bg-white dark:bg-gray-800 border-blue-200 dark:border-blue-700 text-blue-700 dark:text-blue-400': t.type === 'info',
             }">
            <svg x-show="t.type === 'success'" class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="t.type === 'error'" class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            <svg x-show="t.type === 'warning'" class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <svg x-show="t.type === 'info'" class="w-4 h-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="t.msg" class="flex-1"></span>
            <button @click="remove(t.id)" class="opacity-60 hover:opacity-100 ml-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>

@stack('scripts')
<script>
function notifBell() {
    return {
        open: false,
        unread: 0,
        notifications: [],
        load() {
            fetch('{{ route('notifications.index') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(d => { this.notifications = d.notifications; this.unread = d.unread_count; });
        },
        toggle() {
            this.open = !this.open;
            if (this.open) this.load();
        },
        markOne(id) {
            const n = this.notifications.find(x => x.id === id);
            if (n && !n.read) {
                n.read = true;
                this.unread = Math.max(0, this.unread - 1);
                fetch('{{ route('notifications.read') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ id }),
                });
            }
        },
        markAll() {
            this.notifications.forEach(n => n.read = true);
            this.unread = 0;
            fetch('{{ route('notifications.read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({}),
            });
        },
    };
}
</script>
</body>
</html>

<x-app-layout>
    <x-slot name="title">Agenda</x-slot>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Module Agenda</h2>
        <p class="text-gray-500 mt-2">Ce module sera implémenté en Phase 10.</p>
        <a href="{{ route('dashboard') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">← Retour au tableau de bord</a>
    </div>
</x-app-layout>

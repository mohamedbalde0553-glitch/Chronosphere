<x-app-layout>
    <x-slot name="title">Projet / Gantt</x-slot>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Module Projet / Gantt</h2>
        <p class="text-gray-500 mt-2">Ce module sera implémenté en Phase 14.</p>
        <a href="{{ route('dashboard') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">← Retour au tableau de bord</a>
    </div>
</x-app-layout>

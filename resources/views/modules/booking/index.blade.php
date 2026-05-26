<x-app-layout>
    <x-slot name="title">Réservation</x-slot>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-orange-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Module Réservation</h2>
        <p class="text-gray-500 mt-2">Ce module sera implémenté en Phase 12.</p>
        <a href="{{ route('dashboard') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">← Retour au tableau de bord</a>
    </div>
</x-app-layout>

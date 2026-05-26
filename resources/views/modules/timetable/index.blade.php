<x-app-layout>
    <x-slot name="title">Emplois du temps</x-slot>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Module Universitaire</h2>
        <p class="text-gray-500 mt-2">Ce module sera implémenté en Phase 11.</p>
        <a href="{{ route('dashboard') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">← Retour au tableau de bord</a>
    </div>
</x-app-layout>

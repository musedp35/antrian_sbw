<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Notifications
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded p-3 text-green-700">{{ session('success') }}</div>
            @endif
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <a href="{{ route('notifications.mark-all-read') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">Tandai Semua Dibaca</a>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Read At</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th></tr></thead>
                        <tbody class="bg-white divide-y divide-gray-200">@forelse($notifications as $notification)
<tr><td class="px-6 py-4 whitespace-nowrap text-sm">{{ $notification->type }}</td><td class="px-6 py-4 whitespace-nowrap text-sm">{{ $notification->read_at ? $notification->read_at->format('d M Y H:i:s') : 'Belum dibaca' }}</td><td class="px-6 py-4 whitespace-nowrap text-sm">@if(!$notification->read_at)<a href="{{ route('notifications.mark-as-read', $notification->id) }}" class="text-indigo-600 hover:text-indigo-900">Tandai dibaca</a>@endif</td></tr>@empty<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</td></tr>@endforelse</tbody>
                    </table>
                    <div class="mt-4">{{ $notifications->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

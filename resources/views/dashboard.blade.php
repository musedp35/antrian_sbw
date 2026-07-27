<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Antrian') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ stats: { total_today: {{ $totalToday }}, active_count: {{ $activeCount }}, by_type: { spp: {{ $byType['spp'] ?? 0 }}, tunai: {{ $byType['tunai'] ?? 0 }}, tabungan: {{ $byType['tabungan'] ?? 0 }}, by_status: { waiting: {{ ($byStatus['waiting'] ?? 0) }}, called: {{ ($byStatus['called'] ?? 0) }}, served: {{ ($byStatus['served'] ?? 0) }}, cancelled: {{ ($byStatus['cancelled'] ?? 0) }}}} } }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stat Cards Row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Hari Ini -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Tiket Hari Ini</dt>
                                    <dd class="text-2xl font-semibold text-gray-900" x-text="stats.total_today"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aktif (Menunggu + Dipanggil) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Sedang Aktif</dt>
                                    <dd class="text-2xl font-semibold text-gray-900" x-text="stats.active_count"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selesai -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Selesai</dt>
                                    <dd class="text-2xl font-semibold text-gray-900" x-text="stats.by_status?.served || 0"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dibatalkan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-500">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Dibatalkan</dt>
                                    <dd class="text-2xl font-semibold text-gray-900" x-text="stats.by_status?.cancelled || 0"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistik per Tipe --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tiket per Tipe Transaksi</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 text-center">
                            <span class="text-sm text-blue-600 font-medium">SPP</span>
                            <p class="text-3xl font-bold text-blue-800 mt-1" x-text="stats.by_type?.spp || 0"></p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 text-center">
                            <span class="text-sm text-purple-600 font-medium">Tunai</span>
                            <p class="text-3xl font-bold text-purple-800 mt-1" x-text="stats.by_type?.tunai || 0"></p>
                        </div>
                        <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-4 text-center">
                            <span class="text-sm text-teal-600 font-medium">Tabungan</span>
                            <p class="text-3xl font-bold text-teal-800 mt-1" x-text="stats.by_type?.tabungan || 0"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Kasir --}}
            @if($byCashier->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Rekap per Kasir</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kasir</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Tiket</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($byCashier as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item['cashier_name'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-right font-semibold">{{ $item['total'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- 5 Tiket Terakhir --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tiket Terbaru</h3>
                    <div class="space-y-2">
                        @foreach($recentTickets as $ticket)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm font-bold text-gray-900">{{ $ticket->ticket_number }}</span>
                                <span class="text-sm text-gray-500 capitalize">{{ $ticket->type }}</span>
                                <span class="text-sm text-gray-500">
                                    {{ $ticket->assignedCashier?->name ?? '-' }}
                                </span>
                            </div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $ticket->status === 'waiting' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $ticket->status === 'called' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $ticket->status === 'served' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $ticket->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </div>
                        @endforeach
                        @if($recentTickets->isEmpty())
                        <p class="text-center text-gray-500 py-4">Belum ada tiket.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Alpine.js auto-refresh every 5 seconds --}}
        <script>
        setInterval(() => {
            fetch('{{ route('api.dashboard.stats') }}')
                .then(r => r.json())
                .then(data => {
                    const rootEl = document.querySelector('[x-data]');
                    if (rootEl && rootEl.__x) {
                        rootEl.__x.$data.stats = data;
                    }
                })
                .catch(() => {});
        }, 5000);
        </script>
    </div>
</x-app-layout>

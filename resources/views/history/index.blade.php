<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 leading-tight">
            {{ __('Riwayat Panggilan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 mb-1">Total Panggilan</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $callLogs->total() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 mb-1">Dipanggil</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $callLogs->getCollection()->where('ticket.status', 'called')->count() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 mb-1">Selesai</div>
                    <div class="text-2xl font-bold text-green-600">{{ $callLogs->getCollection()->where('ticket.status', 'served')->count() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="text-xs text-gray-500 mb-1">Dibatalkan</div>
                    <div class="text-2xl font-bold text-red-600">{{ $callLogs->getCollection()->where('ticket.status', 'cancelled')->count() }}</div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Filter Data</h3>
                    <form method="GET" action="{{ route('history.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-gray-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-gray-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-200 text-sm">
                                <option value="">Semua</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tipe Transaksi</label>
                            <select name="type" class="w-full rounded-lg border-gray-200 text-sm">
                                <option value="">Semua</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <a href="{{ route('history.index') }}" class="flex-1 text-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm transition-colors">Reset</a>
                            <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm transition-colors">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Riwayat Panggilan - Card Style -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-700">Daftar Panggilan</h3>
                    <span class="text-xs text-gray-500">{{ $callLogs->total() }} data</span>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @forelse($callLogs as $log)
                            @php
                                $ticket = $log->ticket;
                                $status = $ticket->status ?? 'unknown';
                                $type = $ticket->type ?? 'unknown';
                                $iconMap = ['spp' => '🎫', 'tunai' => '💵', 'tabungan' => '🏦'];
                                $icon = $iconMap[$type] ?? '📋';
                                $statusMap = [
                                    'waiting' => ['label' => 'Menunggu', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-200'],
                                    'called' => ['label' => 'Dipanggil', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                                    'served' => ['label' => 'Selesai', 'bg' => 'bg-green-50', 'border' => 'border-green-200'],
                                    'cancelled' => ['label' => 'Dibatalkan', 'bg' => 'bg-red-50', 'border' => 'border-red-200'],
                                ];
                                $statusInfo = $statusMap[$status] ?? $statusMap['waiting'];
                                $cashier = $ticket->assignedCashier ? $ticket->assignedCashier->name : 'Belum di-assign';
                            @endphp
                            <div class="flex items-start gap-4 p-4 rounded-lg border {{ $statusInfo['border'] }} {{ $statusInfo['bg'] }}">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white flex items-center justify-center text-xl shadow-sm">
                                    {{ $icon }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $ticket->ticket_number ?? '-' }}</h4>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ ucfirst($type) }} • {{ $statusInfo['label'] }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="text-xs text-gray-500">{{ $log->played_at->format('d M Y, H:i') }}</div>
                                            <div class="text-xs text-gray-400">{{ $log->played_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 mt-3 pt-3 border-t border-white/50">
                                        <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ $cashier }}
                                        </div>
                                        <div class="ml-auto">
                                            <a href="{{ route('tickets.tts', $ticket) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded text-xs font-medium transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
                                                Replay TTS
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="text-4xl mb-3">📭</div>
                                <p class="text-gray-500">Tidak ada riwayat panggilan</p>
                            </div>
                        @endforelse
                    </div>
                    @if($callLogs->hasPages())
                        <div class="mt-6">{{ $callLogs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
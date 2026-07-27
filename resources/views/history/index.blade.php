<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ 'Riwayat Panggilan' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('history.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div><label for="date_from" class="block text-sm font-medium">Dari Tanggal</label><input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                            <div><label for="date_to" class="block text-sm font-medium">Sampai Tanggal</label><input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                            <div><label for="status" class="block text-sm font-medium">Status</label><select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Semua Status</option>@foreach($statuses as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
                            <div><label for="type" class="block text-sm font-medium">Tipe Transaksi</label><select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Semua Tipe</option>@foreach($types as $t)<option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>@endforeach</select></div>
                        </div>
                        <div class="flex justify-end space-x-2"><a href="{{ route('history.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md">Reset</a><button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">Filter</button></div>
                    </form>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Tiket</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasir</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Panggil</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th></tr></thead>
                        <tbody class="bg-white divide-y divide-gray-200">@forelse($callLogs as $log)<tr>
<td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->ticket->ticket_number }}</td><td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst($log->ticket->type) }}</td><td class="px-6 py-4 whitespace-nowrap text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full @if($log->ticket->status===waiting) bg-yellow-100 text-yellow-800 @elseif($log->ticket->status===called) bg-blue-100 text-blue-800 @elseif($log->ticket->status===served) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">{{ ucfirst($log->ticket->status) }}</span></td><td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->ticket->assignedCashier ? $log->ticket->assignedCashier->name : '-'  }}</td><td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->played_at->format('d M Y H:i:s') }}</td><td class="px-6 py-4 whitespace-nowrap text-sm"><a href="{{ route('tickets.tts', $log->ticket) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Replay TTS</a></td></tr>@empty<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data riwayat panggilan.</td></tr>@endforelse</tbody>
                    </table>
                    <div class="mt-4">{{ $callLogs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

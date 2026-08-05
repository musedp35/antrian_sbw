<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Notifikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Group Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('notifications.delete-read') }}"
                              onsubmit="return confirm('Hapus semua notifikasi yang sudah dibaca?')"
                              class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Hapus yang Dibaca
                            </button>
                        </form>

                        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Tandai Semua Dibaca
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notification List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($notifications as $notification)
                            <div class="flex items-start gap-4 p-4 rounded-lg border
                                        {{ $notification->read_at ? 'border-gray-200 bg-white' : 'border-blue-200 bg-blue-50' }}"
                                 id="notif-{{ $notification->id }}">

                                <!-- Icon -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-xl
                                            {{ $notification->read_at ? 'bg-gray-100' : 'bg-blue-100' }}">
                                    @php
                                        $data = $notification->data ?? [];
                                        $type = $data['type'] ?? null;
                                        $iconMap = [
                                            'spp' => '🎫',
                                            'tunai' => '💵',
                                            'tabungan' => '🏦',
                                            'setting_updated' => '⚙️',
                                            'member_new' => '👤',
                                            'loket_opened' => '🟢',
                                            'loket_closed' => '🔴',
                                            'default' => '🔔',
                                        ];
                                        $icon = $iconMap[$type] ?? $iconMap['default'];
                                    @endphp
                                    {{ $icon }}
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            @php
                                                if ($type === 'member_new') {
                                                    $title = 'Member Baru: ' . ($data['name'] ?? 'Member');
                                                    $message = $data['email'] ?? '';
                                                } elseif (isset($data['title'])) {
                                                    $title = $data['title'];
                                                    $message = $data['description'] ?? '';
                                                } else {
                                                    $ticketNumber = $data['ticket_number'] ?? null;
                                                    $title = $ticketNumber ? 'Tiket Baru: ' . $ticketNumber : 'Notifikasi Baru';
                                                    $typeLabelMap = ['spp' => 'SPP', 'tunai' => 'Tunai', 'tabungan' => 'Tabungan'];
                                                    $message = $type ? 'Tipe: ' . ($typeLabelMap[$type] ?? ucfirst($type)) . ', ' . ($data['created_at'] ?? '') : '';
                                                }
                                            @endphp
                                            <h3 class="text-sm font-medium text-gray-900">
                                                {{ $title }}
                                            </h3>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $message }}
                                            </p>
                                        </div>
                                        <span class="text-xs text-gray-400 whitespace-nowrap">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                        @if(!$notification->read_at)
                                            <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-xs font-medium transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Dibaca
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}"
                                              onsubmit="return confirm('Hapus notifikasi ini?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-medium transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>

                                        <span class="ml-auto text-xs text-gray-400">
                                            {{ $notification->read_at ? '✓ Dibaca' : '🔔 Belum dibaca' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="text-4xl mb-3">📭</div>
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada notifikasi</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

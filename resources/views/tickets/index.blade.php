<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                {{ __('Manajemen Antrian') }}
            </h2>
            <x-primary-button onclick="window.location.href='{{ route('tickets.create') }}'">
                + Tiket Baru
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($tickets->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Tiket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kasir</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($tickets as $ticket)
                                <tr class="{{ $ticket->status === 'called' ? 'bg-yellow-50' : ($ticket->status === 'waiting' ? 'bg-blue-50' : '') }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $ticket->ticket_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 capitalize">{{ $ticket->type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $ticket->status === 'waiting' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $ticket->status === 'called' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $ticket->status === 'served' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $ticket->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ ucfirst($ticket->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->assignedCashier?->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @if($ticket->status === 'waiting')
                                        <button type="button"
                                            data-action="call"
                                            data-ticket-id="{{ $ticket->id }}"
                                            data-call-url="{{ route('tickets.call', $ticket) }}"
                                            class="text-white bg-yellow-500 hover:bg-yellow-600 px-2 py-1 rounded text-xs">
                                            Panggil
                                        </button>
                                        @elseif($ticket->status === 'called')
                                        <button type="button"
                                            data-action="recall"
                                            data-ticket-id="{{ $ticket->id }}"
                                            data-recall-url="{{ route('tickets.recall', $ticket) }}"
                                            class="text-white bg-orange-500 hover:bg-orange-600 px-2 py-1 rounded text-xs"
                                            title="Panggil ulang tiket ini">
                                            🔊 Panggil Ulang
                                        </button>
                                        <button type="button"
                                            data-action="serve"
                                            data-ticket-id="{{ $ticket->id }}"
                                            data-serve-url="{{ route('tickets.serve', $ticket) }}"
                                            class="text-white bg-green-500 hover:bg-green-600 px-2 py-1 rounded text-xs">
                                            Selesai
                                        </button>
                                        @endif
                                        <button type="button"
                                            data-action="cancel"
                                            data-ticket-id="{{ $ticket->id }}"
                                            data-cancel-url="{{ route('tickets.cancel', $ticket) }}"
                                            class="text-red-600 hover:text-red-900 text-xs">
                                            Batal
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-8">Belum ada tiket. Klik "Tiket Baru" untuk memulai.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TTS Playback Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '{{ csrf_token() }}';

        /**
         * Speak text using Web Speech API (Browser TTS)
         */
        function speakText(text, lang = 'id-ID') {
            if (!('speechSynthesis' in window)) {
                console.warn('Browser tidak mendukung Web Speech API');
                return;
            }
            // Cancel any ongoing speech
            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = lang;
            utterance.rate = 0.9;  // Sedikit lambat agar jelas
            utterance.pitch = 1.0;

            // Try to find Indonesian voice
            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(v => v.lang.startsWith('id'));
            if (idVoice) {
                utterance.voice = idVoice;
            }

            window.speechSynthesis.speak(utterance);
        }

        // Ensure voices are loaded
        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = () => {
                window.speechSynthesis.getVoices();
            };
        }

        /**
         * Handle button clicks via AJAX
         */
        document.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();

                const action = this.dataset.action;
                const ticketId = this.dataset.ticketId;
                let url, method;

                switch(action) {
                    case 'call':
                        url = this.dataset.callUrl;
                        break;
                    case 'recall':
                        url = this.dataset.recallUrl;
                        break;
                    case 'serve':
                        url = this.dataset.serveUrl;
                        break;
                    case 'cancel':
                        if (!confirm('Batalkan tiket ini?')) return;
                        url = this.dataset.cancelUrl;
                        break;
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        // TIDAK memutar TTS di Manajemen Antrian (sesuai requirement baru)
                        // TTS hanya diputar di halaman Display oleh polling detection.
                        // Refresh the page after short delay (skip reload untuk recall)
                        if (action !== 'recall') {
                            location.reload();
                        }
                    } else {
                        alert(data.message || 'Terjadi kesalahan.');
                    }
                } catch (err) {
                    console.error('Error:', err);
                    alert('Gagal terhubung ke server.');
                }
            });
        });
    });
    </script>
</x-app-layout>
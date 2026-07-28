<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Tiket - Antrian SBW</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        @media print {
            body > *:not(#ticketModal) { display: none !important; }
            #ticketModal { position: absolute !important; background: white !important; }
            #ticketModal .no-print { display: none !important; }
            #ticketModal .modal-bg-overlay { background: transparent !important; backdrop-filter: none !important; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen flex flex-col items-center justify-center px-4 py-8">

    <div class="w-full max-w-5xl">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-4">
                <img src="{{ asset('images/logos/Logo_Sbw.png') }}" alt="Logo SBW" class="w-full h-full object-contain">
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-gray-800 mb-2">Antrian SBW</h1>
            <p class="text-lg md:text-xl text-gray-600">Koperasi Setia Bhakti Wanita</p>
        </div>

        <!-- Sub Header -->
        <div class="text-center mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800">Pilih Tipe Layanan</h2>
            <p class="text-sm text-gray-500 mt-1">Tekan salah satu kartu di bawah untuk mengambil nomor antrian</p>
        </div>

        <!-- Tombol Pilihan (sejajar horizontal) - AJAX, no form submit -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch" id="ticketButtons">

            <!-- Tombol SPP -->
            <button type="button" data-type="spp"
                class="ticket-card group relative overflow-hidden rounded-2xl bg-white hover:bg-blue-600 border-2 border-blue-300 hover:border-blue-700 shadow-md hover:shadow-2xl px-6 py-12 transition-all duration-300 transform hover:-translate-y-1 cursor-pointer flex flex-col items-center justify-center text-center w-full">
                <!-- Icon Circle -->
                <div class="w-16 h-16 rounded-full bg-blue-100 group-hover:bg-white flex items-center justify-center mb-4 transition-all duration-300">
                    <img src="{{ asset('images/icons/spp.png') }}" alt="SPP" class="w-10 h-10 object-contain">
                </div>
                <!-- Title -->
                <h3 class="text-2xl font-extrabold text-blue-700 group-hover:text-white uppercase tracking-wider mb-1 transition-colors">
                    SPP
                </h3>
                <!-- Subtitle -->
                <p class="text-xs font-medium text-blue-500 group-hover:text-blue-100 transition-colors">
                    Layanan SPP
                </p>
            </button>

            <!-- Tombol Tunai -->
            <button type="button" data-type="tunai"
                class="ticket-card group relative overflow-hidden rounded-2xl bg-white hover:bg-purple-600 border-2 border-purple-300 hover:border-purple-700 shadow-md hover:shadow-2xl px-6 py-12 transition-all duration-300 transform hover:-translate-y-1 cursor-pointer flex flex-col items-center justify-center text-center w-full">
                <!-- Icon Circle -->
                <div class="w-16 h-16 rounded-full bg-purple-100 group-hover:bg-white flex items-center justify-center mb-4 transition-all duration-300">
                    <img src="{{ asset('images/icons/setor_tunai.png') }}" alt="Tunai" class="w-10 h-10 object-contain">
                </div>
                <!-- Title -->
                <h3 class="text-2xl font-extrabold text-purple-700 group-hover:text-white uppercase tracking-wider mb-1 transition-colors">
                    Tunai
                </h3>
                <!-- Subtitle -->
                <p class="text-xs font-medium text-purple-500 group-hover:text-purple-100 transition-colors">
                    Layanan Tunai
                </p>
            </button>

            <!-- Tombol Tabungan -->
            <button type="button" data-type="tabungan"
                class="ticket-card group relative overflow-hidden rounded-2xl bg-white hover:bg-teal-600 border-2 border-teal-300 hover:border-teal-700 shadow-md hover:shadow-2xl px-6 py-12 transition-all duration-300 transform hover:-translate-y-1 cursor-pointer flex flex-col items-center justify-center text-center w-full">
                <!-- Icon Circle -->
                <div class="w-16 h-16 rounded-full bg-teal-100 group-hover:bg-white flex items-center justify-center mb-4 transition-all duration-300">
                    <img src="{{ asset('images/icons/tab_angsuran.png') }}" alt="Tabungan" class="w-10 h-10 object-contain">
                </div>
                <!-- Title -->
                <h3 class="text-2xl font-extrabold text-teal-700 group-hover:text-white uppercase tracking-wider mb-1 transition-colors">
                    Tabungan
                </h3>
                <!-- Subtitle -->
                <p class="text-xs font-medium text-teal-500 group-hover:text-teal-100 transition-colors">
                    Tabungan / Angsuran
                </p>
            </button>
        </div>

        <!-- Info Langkah-langkah -->
        <div class="bg-white rounded-2xl shadow-md p-6 mt-10">
            <h3 class="text-center text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Cara Pengambilan Tiket</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-start space-x-3 p-3 rounded-lg bg-blue-50">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm">1</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Pilih Layanan</p>
                        <p class="text-xs text-gray-500">Klik salah satu kartu layanan di atas</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3 p-3 rounded-lg bg-purple-50">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold text-sm">2</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Cetak Tiket</p>
                        <p class="text-xs text-gray-500">Sistem akan generate nomor antrian (popup)</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3 p-3 rounded-lg bg-teal-50">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold text-sm">3</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Tunggu Panggilan</p>
                        <p class="text-xs text-gray-500">Pantau display untuk nomor Anda</p>
                    </div>
                </div>
            </div>
        </div>

            </div>

    <!-- ============ MODAL POPUP TIKET ============ -->
    <div id="ticketModal" class="hidden fixed inset-0 z-50 items-center justify-center px-4 py-8 modal-bg-overlay bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-md modal-card bg-white rounded-2xl shadow-2xl overflow-hidden border-4 border-dashed"
             id="modalCardBorder">

            <!-- Header Tiket (warna sesuai type) -->
            <div id="modalHeader" class="text-center py-5 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <h1 class="text-base font-medium tracking-wide">KOPERASI SETIA BHAKTI WANITA</h1>
                <p class="text-xs opacity-90 mt-1">Sistem Antrian</p>
            </div>

            <!-- Body Tiket -->
            <div class="p-8 text-center">
                <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">Nomor Antrian Anda</p>

                <div class="my-6">
                    <div id="ticketNumber" class="text-6xl md:text-6xl font-black text-blue-600">
                        -
                    </div>
                </div>

                <div id="ticketBadge" class="inline-block px-5 py-1.5 rounded-full text-white text-base font-semibold bg-blue-500 mt-3 mb-4">
                    SPP
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Tanggal:</strong> <span id="ticketDate">-</span></p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">⚠️ Simpan tiket ini dan tunggu panggilan di layar display</p>
                </div>
            </div>

            <!-- Footer Tiket -->
            <div class="bg-gray-50 py-3 text-center text-xs text-gray-500">
                <p>Terima kasih atas kunjungan Anda</p>
            </div>

            <!-- Tombol Aksi (tidak ikut tercetak) -->
            <div class="no-print mt-6 grid grid-cols-2 gap-3 p-6 pt-0">
                <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak
                </button>
                <button onclick="closeTicketModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg shadow-md transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ambil Lagi
                </button>
            </div>
        </div>

        <!-- Countdown indicator (auto-close) -->
        <div class="no-print fixed bottom-6 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-sm border border-gray-200 rounded-full px-5 py-2 text-xs text-gray-600 shadow-lg flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Otomatis tertutup dalam <strong id="countdownText" class="text-indigo-600">5</strong> detik</span>
        </div>
    </div>

    <!-- CSRF Token Meta (untuk AJAX) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- JavaScript: AJAX Popup + Auto-Close -->
    <script>
        // === Konfigurasi warna per type tiket ===
        const TYPE_CONFIG = {
            spp: {
                label: 'SPP',
                headerGradient: 'bg-gradient-to-r from-blue-500 to-blue-600',
                textColor:       'text-blue-600',
                badgeColor:      'bg-blue-500',
                borderColor:     'border-blue-500',
            },
            tunai: {
                label: 'TUNAI',
                headerGradient: 'bg-gradient-to-r from-purple-500 to-purple-600',
                textColor:       'text-purple-600',
                badgeColor:      'bg-purple-500',
                borderColor:     'border-purple-500',
            },
            tabungan: {
                label: 'TABUNGAN',
                headerGradient: 'bg-gradient-to-r from-teal-500 to-teal-600',
                textColor:       'text-teal-600',
                badgeColor:      'bg-teal-500',
                borderColor:     'border-teal-500',
            },
        };

        // === State untuk auto-close countdown ===
        let countdownTimer = null;
        let countdownRemaining = 30;

        // === Element References ===
        const modal         = document.getElementById('ticketModal');
        const modalHeader   = document.getElementById('modalHeader');
        const modalBorder   = document.getElementById('modalCardBorder');
        const ticketNumberEl = document.getElementById('ticketNumber');
        const ticketBadgeEl  = document.getElementById('ticketBadge');
        const ticketDateEl   = document.getElementById('ticketDate');
        const countdownTextEl = document.getElementById('countdownText');

        /**
         * Submit tiket via AJAX.
         */
        async function generateTicket(type) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('{{ route('members.print') }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ type: type }),
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `HTTP ${response.status}`);
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Gagal membuat tiket.');
                }

                showTicketModal(data.ticket);

            } catch (error) {
                console.error('Generate ticket error:', error);
                alert('Terjadi kesalahan: ' + error.message);
            }
        }

        /**
         * Tampilkan modal popup dengan data tiket.
         */
        function showTicketModal(ticket) {
            const config = TYPE_CONFIG[ticket.type];
            if (!config) {
                console.error('Unknown ticket type:', ticket.type);
                return;
            }

            // Reset class lalu apply config sesuai type
            modalHeader.className   = 'text-center py-6 text-white ' + config.headerGradient;
            modalBorder.className   = 'w-full max-w-md modal-card bg-white rounded-2xl shadow-2xl overflow-hidden border-4 border-dashed ' + config.borderColor;
            ticketNumberEl.className = 'text-6xl md:text-6xl font-black ' + config.textColor;
            ticketBadgeEl.className  = 'inline-block px-5 py-1.5 rounded-full text-white text-base font-semibold ' + config.badgeColor;

            // Isi konten
            ticketNumberEl.textContent = ticket.ticket_number;
            ticketBadgeEl.textContent  = config.label;
            ticketDateEl.textContent   = ticket.created_at;

            // Show modal (centered)
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Start auto-close countdown
            startCountdown();
        }

        /**
         * Tutup modal popup & reset state.
         */
        function closeTicketModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Reset countdown
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }
            countdownRemaining = 5;
            if (countdownTextEl) countdownTextEl.textContent = '5';
        }

        /**
         * Auto-close countdown (30 detik).
         */
        function startCountdown() {
            countdownRemaining = 5;
            if (countdownTextEl) countdownTextEl.textContent = countdownRemaining;

            if (countdownTimer) clearInterval(countdownTimer);

            countdownTimer = setInterval(() => {
                countdownRemaining--;
                if (countdownTextEl) countdownTextEl.textContent = countdownRemaining;

                if (countdownRemaining <= 0) {
                    closeTicketModal();
                }
            }, 1000);
        }

        // === Event Listeners ===
        document.querySelectorAll('#ticketButtons button[data-type]').forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-type');
                generateTicket(type);
            });
        });

        // Close modal saat klik di luar card
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeTicketModal();
            }
        });

        // Close modal dengan Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeTicketModal();
            }
        });
    </script>
</body>
</html>

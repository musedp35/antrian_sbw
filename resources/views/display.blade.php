<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Display Antrian - Sistem Antrian SBW</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Custom utility untuk ticket number - menggunakan CSS variable agar responsif */
        .ticket-display {
            font-weight: 900;
            letter-spacing: 0.15em;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            line-height: 1.1;
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col items-center justify-center text-white font-sans">

    <!-- Header -->
    <div class="text-center mb-8">
        <!-- Logo SBW -->
        <img src="{{ asset('images/logos/Logo_Sbw.png') }}" alt="Logo SBW" class="w-20 h-20 mx-auto mb-3 object-contain opacity-90 hover:opacity-100 transition-opacity duration-300">
        <h1 class="text-4xl md:text-5xl font-bold text-blue-400 mb-1">Sistem Antrian</h1>
        <p class="text-sm md:text-base text-gray-400">Koperasi Setia Bhakti Wanita</p>
    </div>

    <!-- Current Called Ticket -->
    <div id="called-ticket" class="text-center mb-6 min-h-[28vh] sm:min-h-[32vh] md:min-h-[36vh] flex flex-col items-center justify-center">
        <p class="text-lg sm:text-xl md:text-2xl text-gray-400 mb-3 uppercase tracking-wide">Sedang Dipanggil</p>
        <div id="called-number" class="ticket-display text-yellow-400 text-center">-</div>
        <p id="called-type" class="text-xl md:text-2xl mt-3 text-gray-300 capitalize">Menunggu panggilan...</p>
    </div>

    <!-- Next Waiting Tickets - Grid layout with 5+ columns per row for more visible tickets -->
    <div class="w-full max-w-5xl sm:max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-lg sm:text-xl md:text-2xl text-gray-400 mb-3 sm:mb-4 uppercase tracking-wide text-center">Antrian Menunggu</p>
        <div id="waiting-list" class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-7 lg:grid-cols-8 gap-2 sm:gap-3 md:gap-4">
            <p class="text-gray-500 text-center col-span-full py-8">Belum ada antrian menunggu.</p>
        </div>
    </div>

    <!-- Footer time -->
    <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 sm:bottom-6 sm:left-auto sm:right-6 text-gray-300 text-sm sm:text-base px-4 sm:px-6 py-2 sm:py-3 bg-gray-900/80 backdrop-blur-sm rounded-lg backdrop-filter shadow-lg" style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); background-color: rgba(23, 23, 29, 0.8);">
        <span id="clock" class="hidden sm:inline-block mr-3 sm:mr-4"></span>
        <button id="audio-toggle" onclick="toggleAudio()" class="bg-gray-800 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs sm:text-sm border border-gray-600 transition-colors">
            🔊 Audio ON
        </button>
    </div>

    <script>
        // Clock
        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Settings loaded from API (with sensible defaults)
        let appSettings = {
            tts_rate: 0.9,
            tts_volume: 100,
            tts_auto_play: true,
            display_refresh_rate: 2000,
            display_show_countdown: false
        };

        // Fetch settings from API on page load
        async function loadSettings() {
            try {
                const res = await fetch('/api/settings');
                if (res.ok) {
                    const data = await res.json();
                    appSettings = {
                        tts_rate: parseFloat(data.tts_rate) || 0.9,
                        tts_volume: parseInt(data.tts_volume) || 100,
                        tts_auto_play: data.tts_auto_play === 'true' || data.tts_auto_play === true,
                        display_refresh_rate: parseInt(data.display_refresh_rate) || 2000,
                        display_show_countdown: data.display_show_countdown === 'true' || data.display_show_countdown === true
                    };
                    console.log('[Display] Settings loaded:', appSettings);
                }
            } catch (e) {
                console.warn('[Display] Failed to load settings, using defaults:', e);
            }
        }

        // TTS Function untuk Display (Web Speech API)
        let lastCalledTicketId = null;  // Tracking tiket terakhir yang dipanggil (by id)
        let lastCalledUpdatedAt = null;  // Tracking timestamp update (untuk deteksi recall)
        let isSpeechEnabled = true;     // User bisa mute/unmute

        // Toggle audio ON/OFF
        function toggleAudio() {
            isSpeechEnabled = !isSpeechEnabled;
            const btn = document.getElementById('audio-toggle');
            if (isSpeechEnabled) {
                btn.textContent = '🔊 Audio ON';
                btn.classList.remove('bg-red-800', 'hover:bg-red-700');
                btn.classList.add('bg-gray-800', 'hover:bg-gray-700');
                console.log('Audio enabled');
            } else {
                btn.textContent = '🔇 Audio OFF';
                btn.classList.remove('bg-gray-800', 'hover:bg-gray-700');
                btn.classList.add('bg-red-800', 'hover:bg-red-700');
                window.speechSynthesis.cancel(); // Stop ongoing speech
                console.log('Audio disabled');
            }
        }

        function generateTTSText(ticketNumber, type) {
            // Konversi nomor tiket ke format yang bisa dibaca
            // Contoh: "A-001" → "A nol nol satu"
            const parts = ticketNumber.split('-');
            if (parts.length !== 2) return `Nomor antrian ${ticketNumber}, silakan menuju loket`;

            const prefix = parts[0];
            const number = parts[1];
            const digits = {
                '0': 'nol', '1': 'satu', '2': 'dua', '3': 'tiga',
                '4': 'empat', '5': 'lima', '6': 'enam', '7': 'tujuh',
                '8': 'delapan', '9': 'sembilan'
            };
            let readableNumber = prefix;
            for (let digit of number) {
                readableNumber += ' ' + (digits[digit] || digit);
            }

            const typeLabel = {
                'spp': 'S P P',
                'tunai': 'Tunai',
                'tabungan': 'Tabungan'
            }[type] || type;

            return `Nomor antrian ${readableNumber}, silakan menuju loket ${typeLabel}`;
        }

        function speakText(text) {
            if (!appSettings.tts_auto_play || !isSpeechEnabled) {
                console.log('🔇 TTS skipped: disabled by settings or user');
                return;
            }
            if (!('speechSynthesis' in window)) {
                console.warn('⚠️ Browser tidak mendukung Web Speech API');
                return;
            }

            // Cancel speech yang sedang berjalan
            window.speechSynthesis.cancel();
            // PENTING: Tunggu 50ms setelah cancel untuk menghindari bug Chrome
            // di mana utterance baru langsung berakhir tanpa suara
            setTimeout(() => {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = appSettings.tts_rate || 0.9;  // ← DARI SETTING
                utterance.pitch = 1.0;

                // Try to find Indonesian voice
                const voices = window.speechSynthesis.getVoices();
                const idVoice = voices.find(v => v.lang.startsWith('id'));
                if (idVoice) {
                    utterance.voice = idVoice;
                }

                utterance.onstart = () => console.log('🔊 TTS started:', text);
                utterance.onerror = (e) => console.error('❌ TTS error:', e);
                utterance.onend   = () => console.log('✓ TTS finished:', text);

                window.speechSynthesis.speak(utterance);

                // FIX: Web Speech API auto-suspend pada background tab/minimize
                // Firefox & Chrome: speech di-pause > 15 detik akan suspended
                // Solusi: ping synthesis setiap 10 detik untuk jaga utterance tetap hidup
                const keepAlive = setInterval(() => {
                    if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                        window.speechSynthesis.pause();
                        window.speechSynthesis.resume();
                    } else {
                        clearInterval(keepAlive);
                    }
                }, 10000);
            }, 50);
        }

        // Ensure voices are loaded
        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = () => {
                window.speechSynthesis.getVoices();
            };
        }

        // Polling untuk real-time display (tanpa Pusher dulu)
        async function fetchTickets() {
            try {
                const res = await fetch('/api/tickets/display');
                if (!res.ok) return;
                const data = await res.json();

                // Called ticket
                const called = data.called || null;
                document.getElementById('called-number').textContent = called ? called.ticket_number : '-';
                document.getElementById('called-type').textContent = called
                    ? `Tipe: ${called.type}`
                    : 'Menunggu panggilan...';

                // Deteksi tiket baru yang dipanggil → trigger TTS
                if (called) {
                    const currentTicketId = called.id || called.ticket_number;
                    const currentUpdatedAt = called.updated_at || null;

                    // Deteksi apakah perlu trigger TTS:
                    // 1. Call baru: tiket ID BERUBAH (transisi null → id, atau A → B)
                    // 2. Recall: tiket ID SAMA tapi updated_at BERUBAH (akibat $ticket->touch() di recallAjax)
                    const isNewCall = lastCalledTicketId !== null && lastCalledTicketId !== currentTicketId;
                    const isRecall  = lastCalledTicketId === currentTicketId && currentUpdatedAt && lastCalledUpdatedAt !== currentUpdatedAt;

                    // DEBUG: log perubahan state untuk troubleshoot recall
                    console.log('[Display Polling]', {
                        currentTicketId,
                        currentUpdatedAt,
                        lastCalledTicketId,
                        lastCalledUpdatedAt,
                        isNewCall,
                        isRecall,
                        speechEnabled: isSpeechEnabled,
                    });

                    if (isNewCall || isRecall) {
                        const ttsText = generateTTSText(called.ticket_number, called.type);
                        console.log('TTS Triggered:', ttsText, '(isNewCall:', isNewCall, ', isRecall:', isRecall, ')');
                        speakText(ttsText);
                    }

                    lastCalledTicketId = currentTicketId;
                    lastCalledUpdatedAt = currentUpdatedAt;
                    document.getElementById('called-number').parentElement.classList.add('animate-pulse');
                } else {
                    lastCalledTicketId = null;
                    lastCalledUpdatedAt = null;
                }

                // Waiting list - Grid layout with 5+ columns per row for maximum visible tickets
                const waitingEl = document.getElementById('waiting-list');
                if (data.waiting.length > 0) {
                    waitingEl.innerHTML = data.waiting.map(t => `
                        <div class="bg-gray-800 rounded-lg p-2 text-center border border-gray-700 hover:bg-gray-700 transition-colors shadow-sm">
                            <div class="text-xs sm:text-sm font-bold text-blue-300">${t.ticket_number}</div>
                            <div class="text-[10px] text-gray-400 capitalize mt-0.5">${t.type}</div>
                        </div>
                    `).join('');
                } else {
                    waitingEl.innerHTML = '<p class="text-gray-500 text-center col-span-full py-8">Belum ada antrian menunggu.</p>';
                }
            } catch (e) { /* silent fail for polling */ }
        }

        // User Interaction Handler untuk enable audio (beberapa browser butuh ini)
        document.addEventListener('click', function initAudio() {
            // Test kecil untuk "warm up" speech synthesis
            if ('speechSynthesis' in window) {
                const test = new SpeechSynthesisUtterance('');
                test.volume = 0;
                window.speechSynthesis.speak(test);
            }
            document.removeEventListener('click', initAudio);
        }, { once: true });

        // FIX: Beberapa browser pause speechSynthesis saat tab tidak terlihat
        // Saat tab kembali aktif, kita resume agar TTS yang tertunda bisa keluar
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && 'speechSynthesis' in window) {
                if (window.speechSynthesis.paused) {
                    console.log('▶️ Tab visible: resume speechSynthesis');
                    window.speechSynthesis.resume();
                }
            }
        });

        // Load settings first, then start polling
        loadSettings().then(() => {
            fetchTickets();
            // Use setting-defined refresh rate
            setInterval(fetchTickets, appSettings.display_refresh_rate || 2000); // Default 2000ms
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian - Sistem Antrian SBW</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .ticket-display { font-size: 6rem; font-weight: 900; letter-spacing: 4px; }
        @media (max-width: 768px) { .ticket-display { font-size: 3rem; } }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col items-center justify-center text-white">

    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-6xl font-bold text-blue-400 mb-2">Sistem Antrian</h1>
        <p class="text-xl text-gray-400">Koperasi Setia Bhakti Wanita</p>
    </div>

    <!-- Current Called Ticket -->
    <div id="called-ticket" class="text-center mb-12 min-h-[320px] flex flex-col items-center justify-center">
        <p class="text-2xl text-gray-400 mb-4 uppercase tracking-widest">Sedang Dipanggil</p>
        <div id="called-number" class="ticket-display text-yellow-400">-</div>
        <p id="called-type" class="text-3xl mt-4 text-gray-300 capitalize">Menunggu panggilan...</p>
    </div>

    <!-- Next Waiting Tickets -->
    <div class="w-full max-w-3xl px-6">
        <p class="text-xl text-gray-400 mb-4 uppercase tracking-widest text-center">Antrian Menunggu</p>
        <div id="waiting-list" class="grid grid-cols-3 gap-4">
            <p class="text-gray-500 text-center col-span-3 py-8">Belum ada antrian.</p>
        </div>
    </div>

    <!-- Footer time -->
    <div class="fixed bottom-6 text-gray-600 text-sm flex items-center gap-4">
        <span id="clock"></span>
        <button id="audio-toggle" onclick="toggleAudio()" class="bg-gray-800 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs border border-gray-600">
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

        // TTS Function untuk Display (Web Speech API)
        let lastCalledTicketId = null;  // Tracking tiket terakhir yang dipanggil
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
            if (!isSpeechEnabled) return;
            if (!('speechSynthesis' in window)) {
                console.warn('Browser tidak mendukung Web Speech API');
                return;
            }
            // Cancel speech yang sedang berjalan
            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.9;
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

                    // Hanya trigger TTS jika tiket BERUBAH (bukan yang sama)
                    if (lastCalledTicketId !== null && lastCalledTicketId !== currentTicketId) {
                        const ttsText = generateTTSText(called.ticket_number, called.type);
                        console.log('TTS Triggered:', ttsText);
                        speakText(ttsText);
                    }

                    lastCalledTicketId = currentTicketId;
                    document.getElementById('called-number').parentElement.classList.add('animate-pulse');
                } else {
                    lastCalledTicketId = null;
                }

                // Waiting list
                const waitingEl = document.getElementById('waiting-list');
                if (data.waiting.length > 0) {
                    waitingEl.innerHTML = data.waiting.map(t => `
                        <div class="bg-gray-800 rounded-lg p-4 text-center border border-gray-700">
                            <div class="text-2xl font-bold text-blue-300">${t.ticket_number}</div>
                            <div class="text-xs text-gray-400 capitalize mt-1">${t.type}</div>
                        </div>
                    `).join('');
                } else {
                    waitingEl.innerHTML = '<p class="text-gray-500 text-center col-span-3 py-8">Belum ada antrian menunggu.</p>';
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

        fetchTickets();
        setInterval(fetchTickets, 2000); // Refresh setiap 2 detik
    </script>
</body>
</html>
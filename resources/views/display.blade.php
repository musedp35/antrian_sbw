<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Display Antrian - Sistem Antrian SBW</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Responsive logo size utilities (fallback jika Tailwind tidak generate) */
        @media (min-width: 640px) {
            .sm\:w-10 { width: 2.5rem !important; }
            .sm\:w-12 { width: 3rem !important; }
            .sm\:w-14 { width: 3.5rem !important; }
            .sm\:w-16 { width: 4rem !important; }
            .sm\:w-20 { width: 5rem !important; }
            .sm\:w-24 { width: 6rem !important; }
            .sm\:h-10 { height: 2.5rem !important; }
            .sm\:h-12 { height: 3rem !important; }
            .sm\:h-14 { height: 3.5rem !important; }
            .sm\:h-16 { height: 4rem !important; }
            .sm\:h-20 { height: 5rem !important; }
            .sm\:h-24 { height: 6rem !important; }
        }
        @media (min-width: 768px) {
            .md\:w-10 { width: 2.5rem !important; }
            .md\:w-12 { width: 3rem !important; }
            .md\:w-14 { width: 3.5rem !important; }
            .md\:w-16 { width: 4rem !important; }
            .md\:w-20 { width: 5rem !important; }
            .md\:w-24 { width: 6rem !important; }
            .md\:h-10 { height: 2.5rem !important; }
            .md\:h-12 { height: 3rem !important; }
            .md\:h-14 { height: 3.5rem !important; }
            .md\:h-16 { height: 4rem !important; }
            .md\:h-20 { height: 5rem !important; }
            .md\:h-24 { height: 6rem !important; }
        }
        .ticket-type-spp {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }
        .ticket-type-tunai {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        }
        .ticket-type-tabungan {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        }
        .shadow-brutal {
            box-shadow: 6px 6px 0px 0px rgba(203, 213, 225, 1);
        }
        .shadow-brutal-sm {
            box-shadow: 4px 4px 0px 0px rgba(203, 213, 225, 1);
        }
        .aspect-video {
            aspect-ratio: 16 / 9;
        }
        .ticket-card-flex {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border-radius: 0.75rem;
            color: white;
            font-weight: 700;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .ticket-card-flex:hover {
            transform: translateY(-2px);
        }
        @keyframes card-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .card-enter {
            animation: card-enter 0.4s ease forwards;
        }
        @keyframes pulse-called {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }
        .called-pulse {
            animation: pulse-called 1.5s ease-in-out infinite;
        }
        @media (max-width: 768px) {
            .main-layout {
                flex-direction: column;
            }
            .main-layout > section {
                width: 100% !important;
            }
            .waiting-row {
                flex-wrap: wrap;
            }
            .waiting-row > div {
                min-width: calc(50% - 0.5rem);
            }
        }
    </style>
</head>
<body class="bg-white min-h-screen p-4 sm:p-6 flex flex-col font-sans">

    <header class="flex flex-col items-center justify-center mb-6">
        <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mb-2">
            <img src="{{ asset('images/logos/Logo_Sbw.png') }}" alt="Logo SBW" class="w-full h-full object-contain">
        </div>
        <h3 class="text-slate-500 text-sm sm:text-base mt-1">Koperasi Setia Bhakti Wanita</h3>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-800 tracking-tight">Display Antrian</h1>
    </header>

    <main class="main-layout flex flex-row gap-4 sm:gap-6 mb-6 w-full max-w-7xl mx-auto">
        <section id="video-section"
                 class="video-section relative rounded-2xl overflow-hidden border-[6px] border-slate-900 shadow-xl bg-black flex items-center justify-center aspect-video"
                 style="width: 66.666667%;">
            <div id="video-container" class="w-full h-full flex items-center justify-center">
                <p class="text-white text-sm">Memuat video...</p>
            </div>
            <div id="clock-corner" class="absolute bottom-3 right-3 bg-black/80 text-white px-2 py-1 text-xs font-bold rounded z-10">
                <span id="clock-corner-text">--:--:--</span>
            </div>
        </section>

        <section class="called-section bg-slate-100 rounded-2xl shadow-md p-4 sm:p-6 md:p-8 flex flex-col items-center justify-center border border-slate-200"
                 style="width: 33.333333%;">
            <h2 class="text-base sm:text-lg md:text-xl font-bold text-slate-500 mb-3 sm:mb-4 md:mb-6 tracking-wide">SEDANG DIPANGGIL</h2>
            <div id="called-ticket-card"
                 class="called-pulse w-full bg-blue-600 rounded-2xl shadow-brutal py-8 sm:py-10 md:py-14 flex items-center justify-center transition-all duration-300">
                <div class="text-center px-2">
                    <div id="called-number" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white drop-shadow-md leading-tight">-</div>
                    <div id="called-type" class="text-sm sm:text-base md:text-lg text-white/90 mt-2 font-semibold">Menunggu panggilan...</div>
                </div>
            </div>
        </section>
    </main>

    <footer class="w-full max-w-7xl mx-auto">
        <div class="bg-slate-50 rounded-t-xl px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center mb-4">
            <div class="flex items-center gap-3">
                <h3 class="text-xl sm:text-2xl font-bold text-slate-800">Daftar Antrian :</h3>
                {{-- <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 sm:px-3 py-1 rounded-full border border-yellow-200">Hari Ini</span> --}}
            </div>
            <div>
                <span class="bg-white border border-slate-200 text-slate-600 text-xs sm:text-sm font-semibold px-3 sm:px-4 py-1 sm:py-1.5 rounded-full shadow-sm">
                    <span class="text-orange-500 font-bold" id="count-number">0</span> tiket
                </span>
            </div>
        </div>
        <div id="waiting-list" class="waiting-row flex flex-row gap-3 sm:gap-4 w-full min-h-[80px]">
            <p class="text-slate-400 text-center w-full py-6 text-base">Belum ada antrian menunggu.</p>
        </div>
    </footer>

    <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 sm:bottom-6 sm:left-auto sm:right-6 flex items-center gap-2 sm:gap-3 sm:px-4 sm:py-2 sm:bg-white sm:rounded-full sm:border sm:border-gray-200 sm:shadow-md z-50"
         style="background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <span id="clock" class="hidden sm:inline-block text-gray-600 font-mono text-sm sm:text-base px-2 sm:px-3 py-1 sm:py-2 bg-gray-50 rounded-lg border border-gray-200 mr-1 sm:mr-2">HH:mm:ss</span>
        <button id="audio-toggle" onclick="toggleAudio()" class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 bg-white hover:bg-blue-50 text-gray-700 rounded-full border border-gray-200 transition-all duration-300 shadow-sm">
            <span id="audio-icon" class="text-lg sm:text-xl">🔊</span>
            <span id="audio-text" class="hidden sm:inline text-xs sm:text-sm font-medium text-gray-600">Audio</span>
        </button>
    </div>

    <script>
        function updateClock() {
            const now = new Date().toLocaleTimeString('id-ID');
            const clockEl = document.getElementById('clock');
            const clockCornerEl = document.getElementById('clock-corner-text');
            if (clockEl) clockEl.textContent = now;
            if (clockCornerEl) clockCornerEl.textContent = now;
        }
        setInterval(updateClock, 1000);
        updateClock();

        let appSettings = {
            tts_rate: 0.9,
            tts_volume: 100,
            tts_auto_play: true,
            display_refresh_rate: 2000,
            display_show_countdown: false,
            video_enabled: true,
            video_url: 'videos/TokokuSBW.mp4',
            video_autoplay: true,
            video_muted: true,
            video_loop: true,
            video_volume: 100,
            video_poster: null,
        };

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
                        display_show_countdown: data.display_show_countdown === 'true' || data.display_show_countdown === true,
                        video_enabled: data.video_enabled === 'true' || data.video_enabled === true,
                        video_url: data.video_url || 'videos/TokokuSBW.mp4',
                        video_autoplay: data.video_autoplay === 'true' || data.video_autoplay === true,
                        video_muted: data.video_muted === 'true' || data.video_muted === true,
                        video_loop: data.video_loop === 'true' || data.video_loop === true,
                        video_volume: parseInt(data.video_volume) || 100,
                        video_poster: data.video_poster || null,
                    };
                    console.log('[Display] Settings loaded:', appSettings);
                    renderVideoPlayer();
                }
            } catch (e) {
                console.warn('[Display] Failed to load settings, using defaults:', e);
                renderVideoPlayer();
            }
        }

        function renderVideoPlayer() {
            const container = document.getElementById('video-container');
            const section = document.getElementById('video-section');
            const calledSection = document.querySelector('.called-section');

            if (!appSettings.video_enabled) {
                if (section) section.style.display = 'none';
                if (calledSection) calledSection.style.width = '100%';
                console.log('[Display] Video disabled, called ticket takes full width');
                return;
            }

            if (!container) return;

            let videoSrc = appSettings.video_url;
            if (!videoSrc.startsWith('http://') && !videoSrc.startsWith('https://') && !videoSrc.startsWith('/')) {
                videoSrc = '/' + videoSrc;
            }

            container.innerHTML = '<video id="video-player" class="w-full h-full object-cover"'
                + (appSettings.video_autoplay ? ' autoplay' : '')
                + (appSettings.video_muted ? ' muted' : '')
                + (appSettings.video_loop ? ' loop' : '')
                + ' playsinline preload="auto" onerror="handleVideoError(this)">'
                + '<source src="' + videoSrc + '" type="video/mp4">'
                + 'Browser Anda tidak mendukung tag video.'
                + '</video>';

            const videoEl = document.getElementById('video-player');
            if (videoEl) {
                videoEl.volume = (appSettings.video_volume || 100) / 100;

                if (appSettings.video_autoplay) {
                    videoEl.play().catch(err => {
                        console.warn('[Display] Autoplay blocked, waiting for user interaction:', err);
                    });
                }

                document.addEventListener('click', function autoplayFallback() {
                    if (videoEl.paused && appSettings.video_autoplay) {
                        videoEl.play().catch(() => {});
                    }
                }, { once: false });
            }

            console.log('[Display] Video player rendered:', videoSrc);
        }

        function handleVideoError(videoEl) {
            console.error('[Display] Video failed to load');
            const container = document.getElementById('video-container');
            if (container) {
                container.innerHTML = '<div class="text-center text-white p-6">'
                    + '<div class="text-4xl mb-3">🎬</div>'
                    + '<p class="text-base font-semibold mb-1">Video tidak dapat dimuat</p>'
                    + '<p class="text-xs text-white/70">Periksa pengaturan video di halaman Pengaturan</p>'
                    + '</div>';
            }
        }

        let lastCalledTicketId = null;
        let lastCalledUpdatedAt = null;
        let isSpeechEnabled = true;

        function toggleAudio() {
            isSpeechEnabled = !isSpeechEnabled;
            const btn = document.getElementById('audio-toggle');
            const icon = document.getElementById('audio-icon');
            const text = document.getElementById('audio-text');

            if (isSpeechEnabled) {
                icon.textContent = '🔊';
                text.textContent = 'Audio';
                btn.classList.remove('bg-red-100', 'border-red-300', 'text-red-600');
                btn.classList.add('bg-white', 'border-gray-200', 'text-gray-700');
                console.log('[Display] Audio enabled');
            } else {
                icon.textContent = '🔇';
                text.textContent = 'Mute';
                btn.classList.remove('bg-white', 'border-gray-200', 'text-gray-700');
                btn.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
                window.speechSynthesis.cancel();
                // Jika video sedang di-mute untuk TTS, kembalikan sekarang
                restoreVideoAfterTTS();
                console.log('[Display] Audio disabled');
            }
        }

        function generateTTSText(ticketNumber, type) {
            const parts = ticketNumber.split('-');
            if (parts.length !== 2) return 'Nomor antrian ' + ticketNumber + ', silakan menuju loket';

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

            return 'Nomor antrian ' + readableNumber + ', silakan menuju loket ' + typeLabel;
        }

        // === Audio Ducking State (global untuk handle cancel antar TTS) ===
        let videoDuckingActive = false;
        let savedVideoMutedState = null;
        let savedVideoVolume = null;

        function muteVideoForTTS() {
            const videoEl = document.getElementById('video-player');
            if (!videoEl || videoDuckingActive) return;
            if (videoEl.muted || videoEl.paused) return; // sudah mute atau video berhenti, skip

            // Simpan state video saat ini
            savedVideoMutedState = videoEl.muted;
            savedVideoVolume = videoEl.volume;
            videoDuckingActive = true;

            // Mute video
            videoEl.muted = true;
            console.log('[Display] 🔇 Video dimute untuk TTS');
        }

        function restoreVideoAfterTTS() {
            const videoEl = document.getElementById('video-player');
            if (!videoEl || !videoDuckingActive) return;

            // Kembalikan state video
            if (savedVideoMutedState === false) {
                videoEl.muted = false;
                if (savedVideoVolume !== null) {
                    videoEl.volume = savedVideoVolume;
                }
                console.log('[Display] 🔊 Video audio dikembalikan setelah TTS');
            }
            videoDuckingActive = false;
            savedVideoMutedState = null;
            savedVideoVolume = null;
        }

        function speakText(text) {
            if (!appSettings.tts_auto_play || !isSpeechEnabled) {
                console.log('[Display] TTS skipped: disabled by settings or user');
                return;
            }
            if (!('speechSynthesis' in window)) {
                console.warn('[Display] Browser tidak mendukung Web Speech API');
                return;
            }

            window.speechSynthesis.cancel();
            // Jika ada TTS yang sedang berjalan dengan ducking aktif,
            // pastikan tetap mute sampai TTS baru ini selesai
            // (savedVideoMutedState sudah disimpan dari pemanggilan pertama)

            setTimeout(() => {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = appSettings.tts_rate || 0.9;
                utterance.pitch = 1.0;
                utterance.volume = (appSettings.tts_volume || 100) / 100;

                const voices = window.speechSynthesis.getVoices();
                const idVoice = voices.find(v => v.lang.startsWith('id'));
                if (idVoice) {
                    utterance.voice = idVoice;
                }

                // === Audio Ducking: mute video selama TTS ===
                muteVideoForTTS();

                utterance.onstart = () => {
                    console.log('[Display] TTS started:', text);
                    // Pastikan video tetap mute saat TTS mulai
                    const v = document.getElementById('video-player');
                    if (v && !v.muted) {
                        v.muted = true;
                    }
                };
                utterance.onerror = (e) => {
                    console.error('[Display] TTS error:', e);
                    // Kembalikan volume video saat error
                    restoreVideoAfterTTS();
                };
                utterance.onend = () => {
                    console.log('[Display] TTS finished:', text);
                    // Kembalikan video audio setelah TTS selesai
                    restoreVideoAfterTTS();
                };

                window.speechSynthesis.speak(utterance);

                const keepAlive = setInterval(() => {
                    if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                        window.speechSynthesis.pause();
                        window.speechSynthesis.resume();
                        // Pastikan video tetap mute selama TTS berlangsung
                        const v = document.getElementById('video-player');
                        if (v && !v.muted && videoDuckingActive) {
                            v.muted = true;
                        }
                    } else {
                        clearInterval(keepAlive);
                    }
                }, 10000);
            }, 50);
        }

        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = () => {
                window.speechSynthesis.getVoices();
            };
        }

        const typeColors = {
            'spp': { bgStart: '#2563eb', bgEnd: '#1d4ed8', textColor: '#ffffff' },
            'tunai': { bgStart: '#7c3aed', bgEnd: '#6d28d9', textColor: '#ffffff' },
            'tabungan': { bgStart: '#0d9488', bgEnd: '#0f766e', textColor: '#ffffff' }
        };

        async function fetchTickets() {
            try {
                const res = await fetch('/api/tickets/display');
                if (!res.ok) return;
                const data = await res.json();

                const called = data.called || null;
                const calledTicketCard = document.getElementById('called-ticket-card');
                const calledNumberEl = document.getElementById('called-number');
                const calledTypeEl = document.getElementById('called-type');

                if (called && calledTicketCard) {
                    const color = typeColors[called.type] || typeColors['spp'];
                    const typeLabel = called.type_label || (called.type ? called.type.charAt(0).toUpperCase() + called.type.slice(1) : 'Umum');

                    calledTicketCard.style.background = 'linear-gradient(135deg, ' + color.bgStart + ', ' + color.bgEnd + ')';

                    if (calledNumberEl) calledNumberEl.textContent = called.ticket_number;
                    if (calledTypeEl) calledTypeEl.textContent = typeLabel;
                } else {
                    if (calledTicketCard) calledTicketCard.style.background = '#475569';
                    if (calledNumberEl) calledNumberEl.textContent = '-';
                    if (calledTypeEl) calledTypeEl.textContent = 'Menunggu panggilan...';
                }

                if (called) {
                    const currentTicketId = called.id || called.ticket_number;
                    const currentUpdatedAt = called.updated_at || null;

                    const isNewCall = lastCalledTicketId !== null && lastCalledTicketId !== currentTicketId;
                    const isRecall  = lastCalledTicketId === currentTicketId && currentUpdatedAt && lastCalledUpdatedAt !== currentUpdatedAt;

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
                        console.log('[Display] TTS Triggered:', ttsText, '(isNewCall:', isNewCall, ', isRecall:', isRecall, ')');
                        speakText(ttsText);
                    }

                    lastCalledTicketId = currentTicketId;
                    lastCalledUpdatedAt = currentUpdatedAt;
                } else {
                    lastCalledTicketId = null;
                    lastCalledUpdatedAt = null;
                }

                const waitingEl = document.getElementById('waiting-list');
                const countEl = document.getElementById('count-number');

                if (!waitingEl) return;

                if (data.waiting.length > 0) {
                    if (countEl) countEl.textContent = data.waiting.length;

                    const waitingHtml = data.waiting.map((t, index) => {
                        const position = index + 1;
                        const typeClass = 'ticket-type-' + (t.type || 'spp');
                        const typeLabel = t.type_label || (t.type ? t.type.charAt(0).toUpperCase() + t.type.slice(1) : 'Umum');

                        let waitText = '';
                        if (t.created_at) {
                            const created = new Date(t.created_at);
                            const now = new Date();
                            const diffMs = now - created;
                            const diffMin = Math.floor(diffMs / 60000);
                            if (diffMin < 1) waitText = 'Baru';
                            else if (diffMin < 60) waitText = diffMin + ' mnt';
                            else waitText = Math.floor(diffMin / 60) + ' jam';
                        }

                        return '<div class="' + typeClass + ' shadow-brutal-sm ticket-card-flex card-enter flex-1 min-w-[120px]"'
                            + ' title="Antrian ke-' + position + ' - Tipe: ' + typeLabel + ' - Nomor: ' + t.ticket_number + '">'
                            + '<div class="text-center">'
                            + '<div class="text-[10px] font-semibold uppercase opacity-90 mb-1">#' + position + '</div>'
                            + '<div class="text-base sm:text-lg md:text-xl font-extrabold leading-tight drop-shadow-md">' + t.ticket_number + '</div>'
                            + '<div class="text-[10px] font-semibold uppercase mt-1 opacity-90">' + typeLabel + '</div>'
                            + (waitText ? '<div class="text-[9px] mt-0.5 opacity-75">' + waitText + '</div>' : '')
                            + '</div>'
                            + '</div>';
                    }).join('');

                    if (waitingEl.innerHTML !== waitingHtml) {
                        waitingEl.innerHTML = waitingHtml;
                    }
                } else {
                    const emptyHtml = '<p class="text-slate-400 text-center w-full py-6 text-base">Belum ada antrian menunggu.</p>';
                    if (countEl) countEl.textContent = '0';
                    if (waitingEl.innerHTML !== emptyHtml) {
                        waitingEl.innerHTML = emptyHtml;
                    }
                }
            } catch (e) {
                console.warn('[Display] Polling error:', e);
            }
        }

        document.addEventListener('click', function initAudio() {
            if ('speechSynthesis' in window) {
                const test = new SpeechSynthesisUtterance('');
                test.volume = 0;
                window.speechSynthesis.speak(test);
            }
            document.removeEventListener('click', initAudio);
        }, { once: true });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && 'speechSynthesis' in window) {
                if (window.speechSynthesis.paused) {
                    console.log('[Display] Tab visible: resume speechSynthesis');
                    window.speechSynthesis.resume();
                }
            }
        });

        loadSettings().then(() => {
            fetchTickets();
            setInterval(fetchTickets, appSettings.display_refresh_rate || 2000);
        });
    </script>
</body>
</html>

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
        /* === Marquee Animation (Seamless 3-Loop Pattern) === */
        /* Pola seamless profesional dengan 3 pengulangan identik:
           - Konten dirender 3× identik (copy A + copy B + copy C) sebagai .track.
           - translateX(-33.3333%) menggeser tepat 1× lebar copy.
           - Saat copy ke-N+1 tiba di posisi copy ke-N → animasi reset tanpa jeda.
           - Spacing seragam: padding-right pada setiap .track = gap visual.
           - Hasil: 3 kalimat mengalir kontinu, tanpa dempet, tanpa jeda kosong,
             dengan jarak antar kalimat yang seragam. */
        @keyframes marquee-scroll-rtl {
            from { transform: translateX(0); }
            to   { transform: translateX(-33.3333%); }
        }
        @keyframes marquee-scroll-ltr {
            from { transform: translateX(-33.3333%); }
            to   { transform: translateX(0); }
        }
        .marquee-scroll {
            display: inline-flex;
            /* animation properties di-set via inline style oleh JS (renderMarquee):
               - animationName (rtl/ltr)
               - animationDuration (speed dari settings, 5-120s)
               - animationTimingFunction: linear
               - animationIterationCount: infinite
               CSS TIDAK set animation di sini untuk menghindari override JS. */
            width: max-content;
            will-change: transform;
            animation-fill-mode: both;
        }
        /* Setiap copy dibungkus .track; padding-right = gap seragam antar kalimat. */
        .marquee-scroll .track {
            padding-right: 4rem; /* 64px — gap seragam antar kalimat */
            white-space: nowrap;
        }
        /* Marquee container visibility (controlled via data-visible attribute) */
        #marquee-container[data-visible="false"] {
            display: none !important;
        }
        #marquee-container[data-visible="true"] {
            display: flex !important;
        }
        /* Defensive: pastikan marquee-content terlihat (text color putih dari inline style) */
        #marquee-content {
            visibility: visible !important;
            opacity: 1 !important;
        }
        @media (min-width: 769px) {
            /* Desktop: 6 tiket per baris dengan ukuran minimum */
            .waiting-row > div {
                min-width: calc(16.666667% - 0.875rem);
                flex: 1 1 calc(16.666667% - 0.875rem);
            }
        }
        /* === Flip Clock Light Theme (matching display background) === */
        .flip-clock {
            /* Background cream lembut tanpa border (sesuai permintaan: hilangkan outline) */
            background: linear-gradient(145deg, #f8fafc 0%, #e2e8f0 100%);
            border: none;
            box-shadow: none;
            visibility: visible !important;
            opacity: 1 !important;
            display: flex !important;
        }
        .flip-digit {
            display: inline-block;
            position: relative;
            min-width: 1.6ch;
            min-height: 2rem;
            padding: 0.2rem 0.55rem;
            margin: 0 1px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            font-family: 'Courier New', 'Consolas', 'Monaco', monospace;
            font-weight: 900;
            font-size: 1.75rem;
            line-height: 1;
            border-radius: 6px;
            text-align: center;
            vertical-align: middle;
            box-shadow:
                inset 0 -2px 0 rgba(0,0,0,0.6),
                inset 0 1px 0 rgba(255,255,255,0.2),
                0 2px 4px rgba(0,0,0,0.4);
            text-shadow: 0 1px 0 rgba(0,0,0,0.7);
            backface-visibility: visible;
            -webkit-backface-visibility: visible;
            visibility: visible !important;
            opacity: 1 !important;
        }
        /* Flip animation: 2D scale Y (lebih reliable di semua browser) */
        .flip-digit.flip-flash {
            animation: digitFlip 0.65s ease-in-out;
        }
        @keyframes digitFlip {
            0%   { transform: scaleY(1); background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); color: #ffffff; }
            45%  { transform: scaleY(0); background: linear-gradient(180deg, #f9f1dd 0%, #353330 100%); color: #fff; }
            50%  { transform: scaleY(0); background: linear-gradient(180deg, #f9f1dd 0%, #353330 100%); color: #fff; }
            60%  { transform: scaleY(0); background: linear-gradient(180deg, #f9f1dd 0%, #353330 100%); color: #fff; }
            100% { transform: scaleY(1); background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); color: #ffffff; }
        }
        .flip-separator {
            color: #1e293b;
            font-size: 1.6rem;
            font-weight: 900;
            animation: separatorBlink 1s steps(2, end) infinite;
            font-family: 'Courier New', monospace;
            visibility: visible !important;
        }
        @keyframes separatorBlink {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0.4; }
        }
        .flip-digit-group {
            display: inline-flex;
            align-items: center;
            gap: 1px;
        }
        /* Responsive sizing */
        @media (max-width: 640px) {
            .flip-digit { font-size: 1.1rem; padding: 0.1rem 0.35rem; min-width: 1.2ch; }
            .flip-separator { font-size: 1rem; }
        }
        @media (min-width: 1024px) {
            .flip-digit { font-size: 1.85rem; padding: 0.25rem 0.6rem; min-width: 1.6ch; }
            .flip-separator { font-size: 1.7rem; }
        }
        @media (min-width: 1280px) {
            .flip-digit { font-size: 2.1rem; padding: 0.3rem 0.7rem; }
            .flip-separator { font-size: 1.9rem; }
        }

        /* === Fullscreen Desktop Layout === */
        html, body {
            height: 100%;
            overflow: hidden;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        .waiting-row > div {
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
        }
        /* Video section auto-shrink to fit viewport */
        @media (min-width: 1024px) {
            /* Estimate: header ~80px + footer (controls + waiting) ~170px + body padding 24px = ~274px */
            /* Tinggal ~100vh - 274px untuk main */
            #video-section {
                max-height: calc(100vh - 280px);
                width: auto !important;
                flex: 1 1 60%;
                aspect-ratio: 16 / 9;
                height: auto;
                min-height: 0;
            }
            .called-section {
                flex: 1 1 40%;
                min-height: 0;
            }
            .main-layout {
                align-items: stretch;
            }
        }
        @media (max-width: 1023px) {
            #video-section {
                width: 100% !important;
            }
            .called-section {
                width: 100% !important;
            }
        }
        /* Hindari scroll-x di seluruh halaman */
        body, html { overflow-x: hidden; }
    </style>
</head>
<body class="bg-white min-h-screen p-3 sm:p-4 flex flex-col font-sans overflow-x-hidden">

    {{-- === HEADER: Logo (kiri) + Flip Clock Light (kanan) === --}}
    <header class="w-full max-w-7xl mx-auto mb-2 flex-shrink-0 bg-white/70 backdrop-blur-sm rounded-xl px-3 py-2 sm:px-4 sm:py-2 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between gap-3 sm:gap-4">
            {{-- Kiri: Logo + Identitas --}}
            <div class="flex items-center gap-3 sm:gap-4 min-w-0 flex-1">
                <div class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 flex-shrink-0">
                    <img src="{{ asset('images/logos/Logo_Sbw.png') }}" alt="Logo SBW" class="w-full h-full object-contain">
                </div>
                <div class="text-left min-w-0">
                    <h3 class="text-slate-500 text-xs sm:text-sm md:text-base leading-tight truncate">Koperasi Setia Bhakti Wanita</h3>
                    <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-3xl font-extrabold text-slate-800 tracking-tight leading-tight truncate">Display Antrian</h1>
                    {{-- Tanggal lengkap --}}
                    <p id="date-full" class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium truncate">--</p>
                </div>
            </div>

            {{-- Kanan: Flip Clock --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                {{-- Flip Clock Container (visible sm+) --}}
                <div id="flip-clock-wrapper" class="flex items-center" style="visibility: visible; opacity: 1;">
                    <div id="flip-clock" class="flip-clock flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl shadow-md">
                        <div class="flip-digit-group">
                            <span id="flip-hh1" class="flip-digit">0</span><span id="flip-hh2" class="flip-digit">0</span>
                        </div>
                        <span class="flip-separator">:</span>
                        <div class="flip-digit-group">
                            <span id="flip-mm1" class="flip-digit">0</span><span id="flip-mm2" class="flip-digit">0</span>
                        </div>
                        <span class="flip-separator">:</span>
                        <div class="flip-digit-group">
                            <span id="flip-ss1" class="flip-digit">0</span><span id="flip-ss2" class="flip-digit">0</span>
                        </div>
                    </div>
                </div>

                {{-- Mobile fallback clock --}}
                <span id="clock-mobile" class="text-gray-700 font-mono text-sm font-bold bg-gray-50 px-2 py-1 rounded border border-gray-200" style="display:none;">--:--:--</span>
            </div>
        </div>
    </header>

    <main class="main-layout flex flex-row gap-3 sm:gap-4 mb-2 w-full max-w-7xl mx-auto flex-1 min-h-0">
        {{-- Kolom kiri: VIDEO (60%) --}}
        <section id="video-section"
                 class="video-section relative rounded-2xl overflow-hidden border-[6px] border-slate-900 shadow-xl bg-black flex items-center justify-center aspect-video"
                 style="width: 60%;">
            <div id="video-container" class="w-full h-full flex items-center justify-center">
                <p class="text-white text-sm">Memuat video...</p>
            </div>
        </section>

        {{-- Kolom kanan: SEDANG DIPANGGIL (40%) --}}
        <section class="called-section bg-slate-100 rounded-2xl shadow-md p-3 sm:p-4 md:p-5 flex flex-col items-center justify-between border border-slate-200"
                 style="width: 40%;">
            <h2 class="text-base sm:text-lg md:text-xl font-bold text-slate-500 mb-2 sm:mb-3 md:mb-4 tracking-wide">SEDANG DIPANGGIL</h2>
            <div id="called-ticket-card"
                 class="called-pulse w-full bg-blue-600 rounded-2xl shadow-brutal py-6 sm:py-8 md:py-10 flex items-center justify-center transition-all duration-300 flex-1 min-h-0">
                <div class="text-center px-2">
                    <div id="called-number" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white drop-shadow-md leading-tight">-</div>
                    <div id="called-type" class="text-sm sm:text-base md:text-lg text-white/90 mt-2 font-semibold">Menunggu panggilan...</div>
                    <div id="called-loket" class="text-base sm:text-lg md:text-xl text-yellow-200 mt-1 font-bold tracking-wide"></div>
                </div>
            </div>

            {{-- === KONTROL AUDIO & VIDEO (di bawah tiket) === --}}
            <div class="w-full mt-3 sm:mt-4 flex flex-col gap-2">
                <div class="flex items-stretch gap-2 w-full">
                    {{-- 🔊 Audio Toggle --}}
                    <button id="audio-toggle" onclick="toggleAudio()"
                            class="flex-1 flex items-center justify-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 bg-white hover:bg-blue-50 text-gray-700 rounded-lg border border-gray-200 transition-all duration-300 shadow-sm">
                        <span id="audio-icon" class="text-base sm:text-lg">🔊</span>
                        <span id="audio-text" class="text-xs sm:text-sm font-medium text-gray-600">Tiket</span>
                    </button>

                    {{-- 🔊 Video Mute Toggle --}}
                    <button id="video-mute-toggle" onclick="toggleVideoMute()"
                            class="flex-1 flex items-center justify-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 bg-white hover:bg-orange-50 text-gray-700 rounded-lg border border-gray-200 transition-all duration-300 shadow-sm">
                        <span id="video-mute-icon" class="text-base sm:text-lg">🔇</span>
                        <span id="video-mute-text" class="text-xs sm:text-sm font-medium text-gray-600">Video</span>
                    </button>
                </div>

                {{-- Status sistem --}}
                <div class="text-[10px] sm:text-xs text-slate-500 text-center">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        Sistem aktif
                    </span>
                </div>
            </div>
        </section>
    </main>

    {{-- === FOOTER: Daftar Antrian | Running Text (Sisi Kosong) | Total Tiket === --}}
    <footer class="w-full max-w-7xl mx-auto">
        {{-- Baris: Daftar Antrian | Running Text (Sisi Kosong) | Total Tiket --}}
        <div class="bg-slate-50 rounded-t-xl border-b border-slate-200 px-3 sm:px-4 py-2 flex flex-row items-center justify-between gap-2 mb-2 w-full">
            {{-- (1) Daftar Antrian (kiri, fixed) --}}
            <div class="flex items-center flex-shrink-0">
                <h3 class="text-base sm:text-lg md:text-xl font-bold text-slate-800 whitespace-nowrap">Daftar Antrian :</h3>
            </div>

            {{-- (2) Running Text — di Sisi Kosong (tengah, fleksibel) --}}
            <div id="marquee-container" class="flex-1 min-w-0 overflow-hidden bg-gradient-to-r from-slate-100 via-slate-200 to-slate-300 rounded-md border border-white shadow-sm relative mx-2 h-8 flex items-center" data-visible="false">
                <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm z-10 text-blue-900 px-1.5 py-0.5 rounded font-bold">📢</span>
                <div class="overflow-hidden ml-8 sm:ml-9 w-full">
                    <div id="marquee-content" class="marquee-scroll whitespace-nowrap text-sm sm:text-base font-extrabold leading-8 tracking-wide" style="color: #e99300;">
                    </div>
                </div>
            </div>

            {{-- (3) Total Tiket (kanan, fixed) --}}
            <div class="flex-shrink-0">
                <span class="bg-white border border-slate-200 text-slate-600 text-xs sm:text-sm font-semibold px-3 sm:px-4 py-1 sm:py-1.5 rounded-full shadow-sm whitespace-nowrap inline-flex items-center h-8">
                    <span class="text-orange-500 font-bold mr-1" id="count-number">0</span> tiket
                </span>
            </div>
        </div>
        {{-- flex-wrap: agar tiket lebih dari 5 otomatis wrap ke baris baru --}}
        <div id="waiting-list" class="waiting-row flex flex-wrap gap-2 sm:gap-3 w-full min-h-[50px] mb-1 overflow-y-auto" style="max-height: 18vh;">
            <p class="text-slate-400 text-center w-full py-3 text-sm">Belum ada antrian menunggu.</p>
        </div>
    </footer>

    <script>
        function updateClock() {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');
            const timeStr = hh + ':' + mm + ':' + ss;

            // Tanggal lengkap: "Selasa, 29 Juli 2026"
            const dateLong = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            // Tanggal pendek untuk header kanan: "29/07/2026"
            const dateShort = now.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            // Update flip clock (6 digit terpisah) dengan animasi flip top/bottom
            const flipMap = {
                'flip-hh1': hh[0], 'flip-hh2': hh[1],
                'flip-mm1': mm[0], 'flip-mm2': mm[1],
                'flip-ss1': ss[0], 'flip-ss2': ss[1],
            };
            Object.keys(flipMap).forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                const nextVal = flipMap[id];
                const prevVal = el.textContent;
                if (prevVal !== nextVal) {
                    // Trigger flip animation: set textContent dulu agar saat flip mid-point, digit sudah berubah
                    el.textContent = nextVal;
                    el.classList.remove('flip-flash');
                    void el.offsetWidth; // force reflow
                    el.classList.add('flip-flash');
                    setTimeout(() => el.classList.remove('flip-flash'), 650);
                }
            });

            // Update fallback mobile clock
            const clockMobile = document.getElementById('clock-mobile');
            if (clockMobile) clockMobile.textContent = timeStr;

            // Responsive: show flip clock di sm+, mobile fallback di <sm
            const flipWrapper = document.getElementById('flip-clock-wrapper');
            if (flipWrapper && clockMobile) {
                if (window.innerWidth >= 640) {
                    flipWrapper.style.display = 'flex';
                    flipWrapper.style.visibility = 'visible';
                    clockMobile.style.display = 'none';
                } else {
                    flipWrapper.style.display = 'none';
                    clockMobile.style.display = 'inline-block';
                }
            }

            // Update tanggal
            const dateFullEl = document.getElementById('date-full');
            const dateShortEl = document.getElementById('date-short');
            if (dateFullEl) dateFullEl.textContent = dateLong;
            if (dateShortEl) dateShortEl.textContent = dateShort;
        }
        setInterval(updateClock, 1000);
        updateClock();
        // Responsive handler on resize
        window.addEventListener('resize', updateClock);

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
            marquee_text: '',
            marquee_enabled: false,
            marquee_speed: 25,
            marquee_letter_spacing: 0,
            marquee_direction: 'rtl',
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
                        // === VIDEO PLAYLIST (timeline-based, urutan via shuffle/sequential + interval) ===
                        video_playlist: Array.isArray(data.video_playlist) ? data.video_playlist : [],
                        video_playlist_mode: data.video_playlist_mode === 'shuffle' ? 'shuffle' : 'sequential',
                        video_playlist_loop: data.video_playlist_loop === false ? false : true,
                        video_playlist_interval: Math.max(0, parseInt(data.video_playlist_interval) || 0),
                        marquee_text: data.marquee_text || '',
                        marquee_enabled: data.marquee_enabled === 'true' || data.marquee_enabled === true,
                        marquee_speed: parseInt(data.marquee_speed) || 25,
                        marquee_letter_spacing: parseInt(data.marquee_letter_spacing) || 0,
                        marquee_direction: (data.marquee_direction === 'ltr' ? 'ltr' : 'rtl'),
                    };
                    console.log('[Display] Settings loaded:', appSettings);
                    renderVideoPlayer();
                    renderMarquee();
                }
            } catch (e) {
                console.warn('[Display] Failed to load settings, using defaults:', e);
                renderVideoPlayer();
                renderMarquee();
            }
        }

        function renderMarquee() {
            const container = document.getElementById('marquee-container');
            const content = document.getElementById('marquee-content');
            if (!container || !content) {
                console.warn('[Marquee] Container or content element not found');
                return;
            }

            const enabled = appSettings.marquee_enabled && appSettings.marquee_text && appSettings.marquee_text.trim();
            console.log('[Marquee] enabled:', enabled, 'text:', appSettings.marquee_text);

            if (!enabled) {
                // Sembunyikan via data attribute
                container.setAttribute('data-visible', 'false');
                return;
            }

            // Tampilkan via data attribute (CSS handles display)
            container.setAttribute('data-visible', 'true');
            // Escape HTML untuk keamanan
            const escaped = appSettings.marquee_text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // === SEAMLESS 3-LOOP PATTERN ===
            // Render tepat 3 copy identik dibungkus <span class="track">.
            // - Jumlah copy (3) cocok dengan translateX(-33.3333%) sehingga
            //   setiap kali 1 copy lewat, animasi reset ke posisi awal → loop mulus.
            // - Gap antar kalimat di-handle oleh CSS .track { padding-right: 4rem }
            //   sehingga konsisten regardless of text content / letter-spacing.
            // - Tidak ada separator bullet lagi — bullet tumpang tindih dengan padding,
            //   sehingga cukup whitespace saja supaya visual bersih dan seragam.
            const tracks = [];
            for (let i = 0; i < 3; i++) {
                tracks.push('<span class="track">' + escaped + '</span>');
            }
            content.innerHTML = tracks.join('');

            // === Apply Speed (animation-duration), Letter Spacing & Direction dari settings ===
            const speed = Math.max(5, Math.min(120, parseInt(appSettings.marquee_speed) || 25));
            const letterSpacing = Math.max(0, Math.min(20, parseInt(appSettings.marquee_letter_spacing) || 0));
            const direction = appSettings.marquee_direction === 'ltr' ? 'ltr' : 'rtl';

            // === FORCE ANIMATION RESTART ===
            // Browser GPU accelerators sering TIDAK restart animasi hanya karena
            // animationDuration diubah — animasi hanya re-fire saat animationName
            // BERUBAH. Trick: set animationName='none' dulu (pause), tunggu 1 frame,
            // lalu set animationName yang sebenarnya + duration baru.
            // Pola ini 100% reliable di semua browser (Chrome, Firefox, Safari, Edge).
            const targetAnim = direction === 'ltr' ? 'marquee-scroll-ltr' : 'marquee-scroll-rtl';
            content.style.animationName = 'none';
            content.style.animationDuration = '0s';

            // Force reflow agar browser register state 'none' sebelum kita set ulang
            void content.offsetWidth;

            // Pakai setTimeout 0 / requestAnimationFrame agar browser commit
            // state 'none' dulu, baru apply animasi baru.
            requestAnimationFrame(() => {
                content.style.animationName = targetAnim;
                content.style.animationDuration = speed + 's';
                content.style.letterSpacing = letterSpacing + 'px';
                content.style.animationTimingFunction = 'linear';
                content.style.animationIterationCount = 'infinite';
                content.style.paddingLeft = '';
                content.style.paddingRight = '';
                console.log('[Marquee] Animation applied: name=' + targetAnim +
                            ', duration=' + speed + 's, letter-spacing=' + letterSpacing + 'px');
            });
        }

        function renderVideoPlayer() {
            const container = document.getElementById('video-container');
            const section = document.getElementById('video-section');
            const calledSection = document.querySelector('.called-section');

            if (!appSettings.video_enabled) {
                if (section) section.style.display = 'none';
                if (calledSection) {
                    calledSection.style.flex = '1 1 100%';
                    calledSection.style.width = '100%';
                }
                console.log('[Display] Video disabled, called ticket takes full width');
                return;
            }

            if (!container) return;

            // ============================================================
            // PLAYLIST ENGINE
            // ============================================================
            // Build playlist dari appSettings.video_playlist (array of entries)
            // Backward compat: kalau tidak ada, fallback ke video_url (single)
            // ============================================================
            const rawPlaylist = Array.isArray(appSettings.video_playlist) ? appSettings.video_playlist : [];
            const playlist = rawPlaylist
                .filter(entry => entry && entry.enabled !== false) // skip disabled
                .map((entry, idx) => ({
                    path:    entry.path,
                    enabled: entry.enabled !== false,
                    order:   typeof entry.order === 'number' ? entry.order : idx,
                    muted:   typeof entry.muted === 'boolean' ? entry.muted : null,
                    volume:  typeof entry.volume === 'number' ? entry.volume : null,
                }))
                .sort((a, b) => a.order - b.order);

            // Fallback: pakai video_url legacy kalau playlist kosong
            if (playlist.length === 0 && appSettings.video_url) {
                playlist.push({
                    path:    appSettings.video_url,
                    enabled: true,
                    order:   0,
                    muted:   null,
                    volume:  null,
                });
            }

            // Edge case: tidak ada video sama sekali
            if (playlist.length === 0) {
                console.warn('[Display] No video in playlist, hiding video section');
                if (section) section.style.display = 'none';
                if (calledSection) {
                    calledSection.style.flex = '1 1 100%';
                    calledSection.style.width = '100%';
                }
                return;
            }

            // Determine playback order (sequential atau shuffle)
            const mode = appSettings.video_playlist_mode || 'sequential';
            const loopPlaylist = appSettings.video_playlist_loop !== false;
            const interval = Math.max(0, parseInt(appSettings.video_playlist_interval || 0, 10));

            // Untuk shuffle: generate urutan random TANPA pengulangan dalam 1 cycle
            let playOrder = playlist.map((_, i) => i);
            if (mode === 'shuffle') {
                // Fisher-Yates shuffle
                for (let i = playOrder.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [playOrder[i], playOrder[j]] = [playOrder[j], playOrder[i]];
                }
            }

            // Build src list (resolve relative path)
            playOrder = playOrder.map(idx => {
                const entry = playlist[idx];
                let src = entry.path;
                if (!src.startsWith('http://') && !src.startsWith('https://') && !src.startsWith('/')) {
                    src = '/' + src;
                }
                return { ...entry, src };
            });

            // ============================================================
            // RENDER VIDEO ELEMENT (single <video>, reuse untuk semua entry)
            // ============================================================
            // Kita re-render satu kali, lalu pakai ended listener untuk swap source
            // ============================================================
            let useShuffle = mode === 'shuffle';
            let firstSrc = playOrder[0].src;
            const firstEntry = playOrder[0];

            container.innerHTML = '<video id="video-player" class="w-full h-full object-cover"'
                + (appSettings.video_autoplay ? ' autoplay' : '')
                + (appSettings.video_muted ? ' muted' : '')
                + ' playsinline preload="auto" onerror="handleVideoError(this)">'
                + '<source src="' + firstSrc + '" type="video/mp4">'
                + 'Browser Anda tidak mendukung tag video.'
                + '</video>';

            const videoEl = document.getElementById('video-player');
            if (!videoEl) return;

            // Apply per-video override (muted/volume) untuk entry pertama
            // Prioritas: per-video override > global setting > user override (localStorage)
            const initialMuted = firstEntry.muted !== null ? firstEntry.muted : appSettings.video_muted;
            const initialVolume = firstEntry.volume !== null ? firstEntry.volume : (appSettings.video_volume || 100);

            videoEl.volume = initialVolume / 100;
            videoEl.muted = initialMuted;

            // Apply user override mute preference (localStorage) - HANYA untuk button UI,
            // props auto/loop per video tetap prioritas
            try {
                const stored = localStorage.getItem('display_video_muted');
                if (stored === '1') { videoEl.muted = true; videoMutedOverride = true; }
                else if (stored === '0') { videoEl.muted = false; videoMutedOverride = false; }
            } catch(e) {}

            syncVideoMuteUI();

            // ============================================================
            // PLAYLIST STATE & NAVIGATION
            // ============================================================
            let currentIndex = 0;
            let intervalTimer = null;

            function getMIMEType(src) {
                const ext = src.split('.').pop().toLowerCase().split('?')[0];
                return ({
                    'mp4':  'video/mp4',
                    'webm': 'video/webm',
                    'ogg':  'video/ogg',
                    'mov':  'video/quicktime',
                })[ext] || 'video/mp4';
            }

            function playCurrent() {
                const entry = playOrder[currentIndex];
                if (!entry) return;

                const mime = getMIMEType(entry.src);

                // Reset source
                videoEl.pause();
                videoEl.src = entry.src;
                videoEl.type = mime;
                videoEl.load();

                // Apply per-video override
                if (entry.muted !== null) {
                    videoEl.muted = entry.muted;
                } else {
                    videoEl.muted = appSettings.video_muted;
                }
                if (entry.volume !== null) {
                    videoEl.volume = entry.volume / 100;
                } else {
                    videoEl.volume = (appSettings.video_volume || 100) / 100;
                }

                // Re-apply user override (localStorage) supaya button toggle konsisten
                if (videoMutedOverride === true) videoEl.muted = true;
                else if (videoMutedOverride === false) videoEl.muted = false;

                // Play
                if (appSettings.video_autoplay) {
                    videoEl.play().catch(err => {
                        console.warn('[Display] Autoplay blocked:', err);
                    });
                }

                console.log('[Display] Playing:', entry.src, '(index ' + currentIndex + '/' + (playOrder.length - 1) + ')');
            }

            function nextVideo() {
                // Clear interval timer kalau ada
                if (intervalTimer) {
                    clearTimeout(intervalTimer);
                    intervalTimer = null;
                }

                currentIndex++;

                if (currentIndex >= playOrder.length) {
                    if (loopPlaylist) {
                        // Re-shuffle kalau mode shuffle agar tidak monoton
                        if (useShuffle) {
                            for (let i = playOrder.length - 1; i > 0; i--) {
                                const j = Math.floor(Math.random() * (i + 1));
                                [playOrder[i], playOrder[j]] = [playOrder[j], playOrder[i]];
                            }
                        }
                        currentIndex = 0;
                    } else {
                        // Tidak loop, berhenti di akhir
                        console.log('[Display] Playlist finished, no loop');
                        return;
                    }
                }

                // Apply interval delay kalau ada
                if (interval > 0) {
                    intervalTimer = setTimeout(() => {
                        playCurrent();
                    }, interval * 1000);
                } else {
                    playCurrent();
                }
            }

            // ============================================================
            // EVENT LISTENERS
            // ============================================================
            videoEl.addEventListener('ended', nextVideo);

            // Handle video error: skip ke video berikutnya
            videoEl.addEventListener('error', () => {
                console.error('[Display] Video error, skipping to next');
                nextVideo();
            });

            // Initial play
            playCurrent();

            // Fallback autoplay on first user interaction
            document.addEventListener('click', function autoplayFallback() {
                if (videoEl.paused && appSettings.video_autoplay) {
                    videoEl.play().catch(() => {});
                }
            }, { once: false });

            console.log('[Display] Video playlist rendered. Mode:', mode, '| Entries:', playOrder.length, '| Loop:', loopPlaylist, '| Interval:', interval + 's');
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
                text.textContent = 'Tiket';
                btn.classList.remove('bg-red-50', 'border-red-200', 'text-red-600');
                btn.classList.add('bg-white', 'border-gray-200', 'text-gray-700');
                console.log('[Display] Tiket audio enabled');
            } else {
                icon.textContent = '🔇';
                text.textContent = 'Mute';
                btn.classList.remove('bg-white', 'border-gray-200', 'text-gray-700');
                btn.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
                window.speechSynthesis.cancel();
                // Jika video sedang di-mute untuk TTS, kembalikan sekarang
                restoreVideoAfterTTS();
                console.log('[Display] Tiket audio disabled');
            }
        }

        /**
         * Toggle video mute (fungsi terpisah dari audio TTS).
         * Status mute tersimpan di localStorage agar persisten per-browser.
         */
        let videoMutedOverride = null; // null = pakai setting dari DB, true/false = override user
        function toggleVideoMute() {
            const videoEl = document.getElementById('video-player');
            const btn = document.getElementById('video-mute-toggle');
            const icon = document.getElementById('video-mute-icon');
            const text = document.getElementById('video-mute-text');

            // Determine current effective mute state
            let currentMuted;
            if (videoEl) {
                currentMuted = videoEl.muted;
            } else {
                currentMuted = appSettings.video_muted;
            }

            const newMuted = !currentMuted;

            if (videoEl) {
                videoEl.muted = newMuted;
            }
            videoMutedOverride = newMuted;
            try { localStorage.setItem('display_video_muted', newMuted ? '1' : '0'); } catch(e){}

            if (newMuted) {
                icon.textContent = '🔇';
                text.textContent = 'Muted';
                btn.classList.remove('bg-white', 'border-gray-200', 'text-gray-700');
                btn.classList.add('bg-orange-50', 'border-orange-200', 'text-orange-600');
                console.log('[Display] Video muted by user');
            } else {
                icon.textContent = '🔊';
                text.textContent = 'Video';
                btn.classList.remove('bg-orange-50', 'border-orange-200', 'text-orange-600');
                btn.classList.add('bg-white', 'border-gray-200', 'text-gray-700');
                console.log('[Display] Video unmuted by user');
            }
        }

        function syncVideoMuteUI() {
            const btn = document.getElementById('video-mute-toggle');
            const icon = document.getElementById('video-mute-icon');
            const text = document.getElementById('video-mute-text');
            if (!btn) return;

            const videoEl = document.getElementById('video-player');
            const muted = videoEl ? videoEl.muted : (appSettings.video_muted || false);

            if (muted) {
                icon.textContent = '🔇';
                text.textContent = 'Muted';
                btn.classList.add('bg-orange-50', 'border-orange-200', 'text-orange-600');
                btn.classList.remove('bg-white', 'border-gray-200', 'text-gray-700');
            } else {
                icon.textContent = '🔊';
                text.textContent = 'Video';
                btn.classList.remove('bg-orange-50', 'border-orange-200', 'text-orange-600');
                btn.classList.add('bg-white', 'border-gray-200', 'text-gray-700');
            }
        }

        function generateTTSText(ticketNumber, type, loket) {
            const parts = ticketNumber.split('-');
            if (parts.length !== 2) return 'Nomor antrian ' + ticketNumber + ', silakan menuju ' + (loket || 'loket');

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

            // Loket: prioritaskan loket eksplisit, fallback ke label type
            let loketLabel;
            if (loket && typeof loket === 'string' && loket.trim() !== '') {
                // Ubah akronim kapital jadi spelled-out: "Loket SPP" -> "Loket S P P"
                loketLabel = loket.replace(/\b([A-Z]{2,})\b/g, function(m){ return m.split('').join(' '); });
            } else {
                loketLabel = {
                    'spp': 'Loket S P P',
                    'tunai': 'Loket Tunai',
                    'tabungan': 'Loket Tabungan'
                }[type] || 'Loket';
            }

            return 'Nomor antrian ' + readableNumber + ', silakan menuju ' + loketLabel;
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
                const calledLoketEl = document.getElementById('called-loket');

                if (called && calledTicketCard) {
                    const color = typeColors[called.type] || typeColors['spp'];
                    const typeLabel = called.type_label || (called.type ? called.type.charAt(0).toUpperCase() + called.type.slice(1) : 'Umum');

                    calledTicketCard.style.background = 'linear-gradient(135deg, ' + color.bgStart + ', ' + color.bgEnd + ')';

                    if (calledNumberEl) calledNumberEl.textContent = called.ticket_number;
                    if (calledTypeEl) calledTypeEl.textContent = typeLabel;
                    if (calledLoketEl) calledLoketEl.textContent = called.loket ? '→ ' + called.loket : '';
                } else {
                    if (calledTicketCard) calledTicketCard.style.background = '#475569';
                    if (calledNumberEl) calledNumberEl.textContent = '-';
                    if (calledTypeEl) calledTypeEl.textContent = 'Menunggu panggilan...';
                    if (calledLoketEl) calledLoketEl.textContent = '';
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
                        const ttsText = generateTTSText(called.ticket_number, called.type, called.loket);
                        console.log('[Display] TTS Triggered:', ttsText, '(isNewCall:', isNewCall, ', isRecall:', isRecall, ', loket:', called.loket, ')');
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

                // Statistik dihapus dari UI (perubahan layout 100% desktop),
                // tapi data.stats masih diambil dari API untuk caching/kebutuhan lain (TTS countdown, dll).
                if (data.stats) {
                    const setStat = (id, val) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = val;
                    };
                    // Setter di-skip dulu (elemen tidak ada), tapi data stats tetap tersedia.
                }

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

                        return '<div class="' + typeClass + ' shadow-brutal-sm ticket-card-flex card-enter min-w-[120px]"'
                            + ' title="Antrian ke-' + position + ' - Tipe: ' + typeLabel + ' - Nomor: ' + t.ticket_number + '">'
                            + '<div class="text-center">'
                            + '<div class="text-[10px] font-semibold uppercase mt-1 opacity-90">' + typeLabel + '</div>'
                            // + '<div class="text-[10px] font-semibold uppercase opacity-90 mb-1">#' + position + '</div>'
                            + '<div class="text-base sm:text-lg md:text-xl font-extrabold leading-tight drop-shadow-md">' + t.ticket_number + '</div>'
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
            // === SETTINGS POLL ===
            // Poll settings setiap 5 detik supaya perubahan dari admin panel
            // (speed, letter-spacing, direction, text, video, dll) live-applied.
            // Hanya re-render yang relevan untuk menghindari flickering.
            setInterval(async () => {
                try {
                    const res = await fetch('/api/settings', { cache: 'no-store' });
                    if (!res.ok) return;
                    const data = await res.json();
                    const newSettings = {
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
                        marquee_text: data.marquee_text || '',
                        marquee_enabled: data.marquee_enabled === 'true' || data.marquee_enabled === true,
                        marquee_speed: parseInt(data.marquee_speed) || 25,
                        marquee_letter_spacing: parseInt(data.marquee_letter_spacing) || 0,
                        marquee_direction: (data.marquee_direction === 'ltr' ? 'ltr' : 'rtl'),
                    };
                    // Detect perubahan marquee-relevant → trigger re-render
                    const changed =
                        newSettings.marquee_speed !== appSettings.marquee_speed ||
                        newSettings.marquee_letter_spacing !== appSettings.marquee_letter_spacing ||
                        newSettings.marquee_direction !== appSettings.marquee_direction ||
                        newSettings.marquee_text !== appSettings.marquee_text ||
                        newSettings.marquee_enabled !== appSettings.marquee_enabled;
                    appSettings = newSettings;
                    if (changed) {
                        console.log('[Display] Marquee settings changed → re-rendering. New speed:', appSettings.marquee_speed);
                        renderMarquee();
                    }
                    // Detect video change → re-render video
                    if (newSettings.video_url !== appSettings.video_url ||
                        newSettings.video_enabled !== appSettings.video_enabled) {
                        renderVideoPlayer();
                    }
                } catch (e) {
                    // silent — settings poll failure should not spam console
                }
            }, 5000);
        });
    </script>
</body>
</html>

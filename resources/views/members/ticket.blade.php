<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket {{ $ticket->ticket_number }} - Antrian SBW</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex flex-col items-center justify-center px-4">

    @php
        // Tentukan style config berdasarkan tipe tiket (DRY principle)
        $typeConfig = match($ticket->type) {
            'spp' => [
                'label'      => 'SPP',
                'border'     => 'border-blue-500',
                'header'     => 'bg-gradient-to-r from-blue-500 to-blue-600',
                'textColor'  => 'text-blue-600',
                'badge'      => 'bg-blue-500',
            ],
            'tunai' => [
                'label'      => 'TUNAI',
                'border'     => 'border-purple-500',
                'header'     => 'bg-gradient-to-r from-purple-500 to-purple-600',
                'textColor'  => 'text-purple-600',
                'badge'      => 'bg-purple-500',
            ],
            'tabungan' => [
                'label'      => 'TABUNGAN',
                'border'     => 'border-teal-500',
                'header'     => 'bg-gradient-to-r from-teal-500 to-teal-600',
                'textColor'  => 'text-teal-600',
                'badge'      => 'bg-teal-500',
            ],
            default => [
                'label'      => strtoupper($ticket->type),
                'border'     => 'border-gray-500',
                'header'     => 'bg-gradient-to-r from-gray-500 to-gray-600',
                'textColor'  => 'text-gray-600',
                'badge'      => 'bg-gray-500',
            ],
        };
    @endphp

    <div class="w-full max-w-md">
        <!-- Card Tiket -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border-4 border-dashed {{ $typeConfig['border'] }}">

            <!-- Header Tiket -->
            <div class="text-center py-6 {{ $typeConfig['header'] }} text-white">
                <h1 class="text-2xl font-bold">KOPERASI SBW</h1>
                <p class="text-sm opacity-90">Sistem Antrian</p>
            </div>

            <!-- Body Tiket -->
            <div class="p-8 text-center">
                <p class="text-sm text-gray-500 uppercase tracking-widest mb-2">Nomor Antrian Anda</p>

                <div class="my-6">
                    <div class="text-7xl md:text-8xl font-black {{ $typeConfig['textColor'] }}">
                        {{ $ticket->ticket_number }}
                    </div>
                </div>

                <div class="inline-block px-6 py-2 rounded-full text-white text-lg font-semibold {{ $typeConfig['badge'] }}">
                    {{ $typeConfig['label'] }}
                </div>

                <div class="mt-6 text-sm text-gray-600">
                    <p>Tanggal: <strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong></p>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200 text-xs text-gray-500">
                    <p>⚠️ Simpan tiket ini dan tunggu panggilan di layar display</p>
                </div>
            </div>

            <!-- Footer Tiket -->
            <div class="bg-gray-50 py-3 text-center text-xs text-gray-500">
                <p>Terima kasih atas kunjungan Anda</p>
            </div>
        </div>

        <!-- Tombol Aksi (tidak ikut tercetak) -->
        <div class="no-print mt-6 grid grid-cols-2 gap-3">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md transition flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>
            <a href="{{ route('members.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg shadow-md transition flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ambil Lagi
            </a>
        </div>

        <!-- Info tambahan -->
        <div class="no-print mt-6 text-center text-sm">
            <a href="{{ route('display') }}" target="_blank" class="text-indigo-600 hover:underline">📺 Lihat Display Antrian</a>
            <span class="mx-2 text-gray-400">|</span>
            <a href="{{ route('login') }}" class="text-gray-600 hover:underline">Login Admin</a>
        </div>
    </div>

    <script>
        // Auto-print setelah halaman dimuat (opsional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>

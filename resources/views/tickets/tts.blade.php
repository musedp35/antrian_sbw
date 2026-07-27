<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TTS - {{ $ticket->ticket_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-900 min-h-screen flex flex-col items-center justify-center text-white">
    <div class="text-center">
        <h1 class="text-4xl mb-4">Memanggil Antrian</h1>
        <div class="mb-8">
            <p class="text-6xl font-bold text-yellow-400 mb-2">{{ '000 ' . $ticket->ticket_number }}</p>
            <p class="text-3xl capitalize text-blue-400">{{ ucfirst($ticket->type) }}</p>
        </div>
        <div class="space-x-4">
            @if($ticket->status === 'called')
                <form action="{{ route('tickets.serve', $ticket) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-500 hover:bg-green-600 rounded-lg text-lg font-semibold">Selesai (Serve)</button>
                </form>
            @endif
            <a href="{{ route('tickets.index') }}" class="inline-block px-6 py-3 bg-gray-600 hover:bg-gray-700 rounded-lg text-lg">Kembali</a>
        </div>
    </div>
    <script>
        // Auto-play TTS
        const queueText = '{{ $ticket->ticket_number }} untuk layanan {{ ucfirst($ticket->type) }}';
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(queueText);
            utterance.lang = 'id-ID';
            utterance.rate = 0.9;
            speechSynthesis.speak(utterance);
        }
    </script>
</body>
</html>
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            {{ __('Buat Tiket Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Role badge --}}
                    <div class="mb-4 text-sm text-gray-500">
                        Login sebagai: <span class="font-semibold text-gray-700">{{ str_replace('_', ' ', ucwords(auth()->user()->role)) }}</span>
                    </div>

                    <form method="POST" action="{{ route('tickets.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="type" :value="__('Tipe Transaksi')" />
                            @if(count($allowedTypes) === 1)
                                {{-- Single type — auto-selected --}}
                                @php $firstKey = array_key_first($allowedTypes); @endphp
                                <input type="hidden" name="type" value="{{ $firstKey }}">
                                <div class="mt-2 px-4 py-3 bg-gray-100 rounded-md border border-gray-300 text-gray-800 font-medium">
                                    {{ $allowedTypes[$firstKey]['label'] }}
                                </div>
                            @else
                                <select id="type" name="type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    <option value="">-- Pilih Tipe --</option>
                                    @foreach($allowedTypes as $key => $info)
                                        <option value="{{ $key }}">{{ $info['label'] }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>{{ __('Buat Tiket') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
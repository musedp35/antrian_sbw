<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PATCH')

                @foreach($groups as $groupKey => $group)
                    @if(count($group['settings']) > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">{{ $group['title'] }}</h3>
                            <div class="space-y-4">
                                @foreach($group['settings'] as $s)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $s['key'])) }}
                                        </label>
                                        @if($s['type'] === 'boolean')
                                            <select name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]"
                                                    class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="true" {{ $s['value'] === 'true' || $s['value'] === true ? 'selected' : '' }}>Aktif</option>
                                                <option value="false" {{ $s['value'] === 'false' || $s['value'] === false ? 'selected' : '' }}>Non-Aktif</option>
                                            </select>
                                        @elseif($s['type'] === 'number')
                                            <input type="number" step="0.1" min="0" max="200"
                                                   name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]"
                                                   value="{{ $s['value'] }}"
                                                   class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @else
                                            <input type="text"
                                                   name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]"
                                                   value="{{ $s['value'] }}"
                                                   class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @endif
                                        @if($s['desc'])
                                            <p class="mt-1 text-xs text-gray-500">{{ $s['desc'] }}</p>
                                        @endif
                                        {{-- Hidden key field --}}
                                        <input type="hidden" name="settings[{{ $loop->parent->index * 100 + $loop->index }}][key]" value="{{ $s['key'] }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Simpan Pengaturan') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
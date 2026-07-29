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
                                            {{ $s['label'] ?? ucfirst(str_replace('_', ' ', $s['key'])) }}
                                        </label>
                                        @if($s['type'] === 'boolean')
                                            <select name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]"
                                                    class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="true" {{ $s['value'] === 'true' || $s['value'] === true ? 'selected' : '' }}>Aktif</option>
                                                <option value="false" {{ $s['value'] === 'false' || $s['value'] === false ? 'selected' : '' }}>Non-Aktif</option>
                                            </select>
                                        @elseif(!empty($s['select_options']))
                                            {{-- Setting dengan opsi terbatas (select) --}}
                                            <select name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]"
                                                    class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                @foreach($s['select_options'] as $optKey => $optLabel)
                                                    <option value="{{ $optKey }}" {{ (string)$s['value'] === (string)$optKey ? 'selected' : '' }}>
                                                        {{ $optLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($s['type'] === 'number')
                                            <input type="number" {{ $s['input_attrs'] ?? 'min="0" max="200" step="0.1"' }}
                                                   name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]"
                                                   value="{{ $s['value'] }}"
                                                   class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @elseif($s['type'] === 'json')
                                            {{-- JSON-type setting: managed via dedicated UI (e.g. video_playlist). Hide the raw form row but keep it editable via custom UI below. --}}
                                            <div class="text-xs text-gray-500 italic">
                                                📋 Disimpan otomatis oleh <strong>Video Playlist Manager</strong> di bawah. (JSON: <span class="font-mono">{{ count((array)$s['value']) }} item</span>)
                                            </div>
                                            <input type="hidden" name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]" value="">
                                            <input type="hidden" name="settings[{{ $loop->parent->index * 100 + $loop->index }}][key]" value="{{ $s['key'] }}">
                                        @elseif($s['key'] === 'marquee_text')
                                            {{-- Marquee: gunakan textarea untuk teks panjang --}}
                                            <textarea name="settings[{{ $loop->parent->index * 100 + $loop->index }}][value]"
                                                      rows="3"
                                                      class="block w-full md:w-3/4 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm"
                                                      placeholder="Tulis pengumuman / running text di sini...">{{ $s['value'] }}</textarea>
                                            <p class="mt-1 text-xs text-gray-500">💡 Teks akan berjalan di bagian samping Daftar Antrian.</p>
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

                {{-- =================================================== --}}
                {{-- VIDEO PLAYLIST MANAGER (Custom UI, di luar form loop) --}}
                {{-- =================================================== --}}
                @php
                    // Cari setting video_playlist untuk render di section ini
                    $playlistSetting = null;
                    foreach ($groups as $gk => $g) {
                        foreach ($g['settings'] as $s) {
                            if ($s['key'] === 'video_playlist') {
                                $playlistSetting = $s;
                                break 2;
                            }
                        }
                    }
                    $currentPlaylist = is_array($playlistSetting['value'] ?? null) ? $playlistSetting['value'] : [];
                @endphp
                @if($playlistSetting)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="videoPlaylistManager()" x-init="init()">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center gap-2">
                            <span>🎬</span> Video Playlist
                            <span class="ml-auto text-xs font-normal text-gray-500" x-text="`${playlist.length} video`"></span>
                        </h3>

                        <p class="text-sm text-gray-600 mb-4">
                            Kelola playlist video yang akan diputar di display. Urutan & status aktif/non-aktif bisa diubah di sini.
                        </p>

                        {{-- Playlist Editor (drag-drop reorder) --}}
                        <div class="space-y-2 mb-4">
                            <template x-for="(item, idx) in playlist" :key="item.path">
                                <div class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                                    {{-- Drag handle --}}
                                    <div class="cursor-move text-gray-400 hover:text-gray-600"
                                         @click="moveUp(idx)" title="Pindah ke atas">▲</div>
                                    <div class="cursor-move text-gray-400 hover:text-gray-600"
                                         @click="moveDown(idx)" title="Pindah ke bawah">▼</div>

                                    {{-- Order index --}}
                                    <div class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full font-bold text-sm"
                                         x-text="idx + 1"></div>

                                    {{-- Video info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="font-mono text-sm text-gray-800 truncate" :title="item.path" x-text="item.path"></div>
                                        <div class="text-xs text-gray-500" x-text="`Size: ${item.size_human || '?'}`"></div>
                                    </div>

                                    {{-- Per-video override (muted/volume) --}}
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-1 text-xs text-gray-600" title="Mute video ini (override global)">
                                            <input type="checkbox" x-model="item.muted" @change="markDirty()"
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span>Mute</span>
                                        </label>
                                    </div>

                                    {{-- Enabled toggle --}}
                                    <label class="flex items-center gap-1 text-xs" title="Aktif/non-aktif video ini">
                                        <input type="checkbox" x-model="item.enabled" @change="markDirty()"
                                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="text-gray-700 font-medium">Aktif</span>
                                    </label>

                                    {{-- Remove button --}}
                                    <button type="button" @click="removeFromPlaylist(item.path)"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded text-sm"
                                            title="Hapus dari playlist (file tidak dihapus)">
                                        ✕
                                    </button>
                                </div>
                            </template>

                            {{-- Empty state --}}
                            <div x-show="playlist.length === 0" class="text-center py-8 text-gray-400">
                                <div class="text-3xl mb-2">📭</div>
                                <div class="text-sm">Playlist kosong. Upload video di bawah untuk menambah.</div>
                            </div>
                        </div>

                        {{-- Upload Section --}}
                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">📤 Upload Video Baru</h4>
                            <div class="flex items-center gap-2">
                                <input type="file" id="video-upload-input" accept="video/mp4,video/webm,video/ogg,video/mov"
                                       class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <button type="button" @click="uploadVideo()" :disabled="uploading"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                    <span x-show="!uploading">Upload</span>
                                    <span x-show="uploading">⏳ Uploading...</span>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                💡 Format: MP4, WebM, OGG, MOV. Maks 200MB. Video otomatis ditambahkan ke playlist setelah upload.
                            </p>
                            <div x-show="uploadError" x-text="uploadError" class="mt-2 text-sm text-red-600"></div>
                            <div x-show="uploadSuccess" x-text="uploadSuccess" class="mt-2 text-sm text-green-600"></div>
                        </div>

                        {{-- Available videos (yang belum di playlist) --}}
                        <div class="border-t pt-4 mt-4" x-show="availableVideos.length > 0">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">📂 Video Tersedia (belum di playlist)</h4>
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                <template x-for="v in availableVideos" :key="v.path">
                                    <div class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded text-sm hover:bg-gray-50">
                                        <div class="flex-1 truncate font-mono" x-text="v.name"></div>
                                        <div class="text-xs text-gray-500" x-text="v.size_human"></div>
                                        <button type="button" @click="addToPlaylist(v)"
                                                class="px-2 py-1 bg-green-50 text-green-700 hover:bg-green-100 rounded text-xs font-medium">
                                            + Tambah
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Hidden input untuk submit playlist (JSON encoded) --}}
                        <input type="hidden" name="settings_json[video_playlist]" :value="playlistJsonString">

                        {{-- Save indicator --}}
                        <div x-show="isDirty" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                            ⚠️ Ada perubahan yang belum disimpan. Klik <strong>Simpan Pengaturan</strong> di bawah.
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-end">
                    <x-primary-button>{{ __('Simpan Pengaturan') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- VIDEO PLAYLIST MANAGER — Alpine.js Component --}}
    {{-- ============================================================ --}}
    <script>
        function videoPlaylistManager() {
            return {
                playlist: @json($currentPlaylist),
                availableVideos: [],
                uploading: false,
                uploadError: '',
                uploadSuccess: '',
                isDirty: false,

                get playlistJsonString() {
                    return JSON.stringify(this.playlist.map((item, idx) => ({
                        path:    item.path,
                        enabled: item.enabled !== false,
                        order:   idx,
                        muted:   item.muted || false,
                    })));
                },

                async init() {
                    // Fetch available videos
                    try {
                        const res = await fetch('/api/videos/available');
                        const all = await res.json();
                        const playlistPaths = new Set(this.playlist.map(p => p.path));
                        this.availableVideos = all.filter(v => !playlistPaths.has(v.path));
                    } catch (e) {
                        console.warn('Could not load available videos:', e);
                    }
                },

                markDirty() {
                    this.isDirty = true;
                },

                moveUp(idx) {
                    if (idx <= 0) return;
                    [this.playlist[idx - 1], this.playlist[idx]] = [this.playlist[idx], this.playlist[idx - 1]];
                    this.markDirty();
                },

                moveDown(idx) {
                    if (idx >= this.playlist.length - 1) return;
                    [this.playlist[idx], this.playlist[idx + 1]] = [this.playlist[idx + 1], this.playlist[idx]];
                    this.markDirty();
                },

                removeFromPlaylist(path) {
                    if (!confirm('Hapus video ini dari playlist? File tidak dihapus, hanya dikeluarkan dari playlist.')) return;
                    this.playlist = this.playlist.filter(p => p.path !== path);
                    this.markDirty();
                    // Refresh available videos
                    this.init();
                },

                async addToPlaylist(video) {
                    // Cek apakah sudah ada
                    if (this.playlist.some(p => p.path === video.path)) {
                        alert('Video sudah ada di playlist.');
                        return;
                    }
                    this.playlist.push({
                        path:    video.path,
                        enabled: true,
                        order:   this.playlist.length,
                        muted:   false,
                        size_human: video.size_human,
                    });
                    this.markDirty();
                    // Refresh available
                    this.availableVideos = this.availableVideos.filter(v => v.path !== video.path);
                },

                async uploadVideo() {
                    const input = document.getElementById('video-upload-input');
                    if (!input.files.length) {
                        this.uploadError = 'Pilih file video terlebih dahulu.';
                        return;
                    }
                    this.uploading = true;
                    this.uploadError = '';
                    this.uploadSuccess = '';

                    const formData = new FormData();
                    formData.append('video', input.files[0]);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    try {
                        const res = await fetch('/settings/videos/upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.uploadSuccess = `✅ Upload berhasil: ${data.video.name} (${data.video.size_human})${data.added_to_playlist ? ' — otomatis ditambahkan ke playlist' : ''}.`;
                            input.value = ''; // reset
                            // Refresh list
                            await this.init();
                            // Reload page after 2 detik supaya playlist dari server dimuat
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            this.uploadError = data.message || 'Upload gagal.';
                        }
                    } catch (e) {
                        this.uploadError = 'Network error: ' + e.message;
                    } finally {
                        this.uploading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
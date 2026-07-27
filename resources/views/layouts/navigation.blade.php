<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if(auth()->check())
                    <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-1 rounded-md hidden lg:inline-flex" title="{{ auth()->user()->role }}">
                        {{ str_replace('_', ' ', ucwords(auth()->user()->role)) }}
                    </span>
                    @endif
                    <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                        {{ __('Antrian') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('display') }}" target="_blank">
                        {{ __('Display') }}
                    </x-nav-link>
                    @role('super_admin')
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('User Management') }}
                    </x-nav-link>
                    <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                        {{ __('Pengaturan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('history.index')" :active="request()->routeIs('history.*')">
                        {{ __('Riwayat') }}
                    </x-nav-link>
                    @enderror
            <button type="button" class="relative inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 p-2 ms-2"
                    x-data="{ unreadCount: 0 }"
                    x-init="setInterval(() => { fetch('{{ route('api.notifications.unread-count') }}').then(r => r.json()).then(d => unreadCount=d.count).catch(()=>{}); }, 5000);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
            </button>
            <a href="{{ route('notifications.index') }}" class="ml-1 text-xs text-gray-500 hover:text-indigo-600 self-center">Lihat semua</a>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Show role badge --}}
                        <div class="px-4 py-2 text-xs text-gray-400 border-b border-gray-100">
                            Role: <span class="font-semibold text-gray-700">{{ str_replace('_', ' ', ucwords(auth()->user()->role)) }}</span>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                {{ __('Antrian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('display') }}" target="_blank">
                {{ __('Display') }}
            </x-responsive-nav-link>
            @role('super_admin')
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('User Management') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                {{ __('Pengaturan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('history.index')" :active="request()->routeIs('history.*')">
                {{ __('Riwayat') }}
            </x-responsive-nav-link>
            @enderror
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

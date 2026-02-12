<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Events - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Preline -->
    <link rel="stylesheet" href="https://preline.co/assets/css/preline.css">

    <!-- Styles / Scripts -->
    
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>
<body class="bg-slate-50">
    <!-- Navbar -->
    <nav class="sticky top-0 inset-x-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-lg font-bold text-slate-900">Events</span>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('home') }}" class="text-slate-700 hover:text-blue-600 font-medium text-sm transition">
                            {{ __('messages.home') }}
                        </a>
                        <div class="hs-dropdown relative inline-flex">
                            <button type="button" class="hs-dropdown-toggle flex items-center gap-2 text-slate-700 hover:text-blue-600 font-medium text-sm transition">
                                {{ Auth::user()->name }}
                                <svg class="hs-dropdown-open:rotate-180 w-4 h-4 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                </svg>
                            </button>
                            <div class="hs-dropdown-menu transition-[opacity,margin] duration-[0.1ms] hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg p-2 before:absolute before:-top-4 before:left-0 before:w-full before:h-4">
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-3 py-2 px-3 rounded-md text-sm text-slate-700 hover:bg-slate-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        {{ __('messages.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-700 hover:text-blue-600 font-medium text-sm transition">
                            {{ __('messages.login') }}
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm transition">
                                {{ __('messages.register') }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Events
                    </h3>
                    <p class="text-slate-400 text-sm">Discover amazing events in your community.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Browse Events</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="#" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 flex justify-between items-center">
                <p class="text-slate-400 text-sm">&copy; 2026 Events. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20v-7.21H5.5V9.25h2.79V7.07c0-2.77 1.69-4.28 4.16-4.28 1.18 0 2.2.09 2.49.13v2.88h-1.71c-1.34 0-1.6.64-1.6 1.57v2.05h3.2l-.41 3.54h-2.79V20h-2.79z"/></svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2s9 5 20 5a9.5 9.5 0 00-9-5.5c4.75 2.25 7-7 7-7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Preline JS -->
    <script src="https://preline.co/assets/js/preline.js"></script>
    <script>
        // Initialize Preline components
        document.addEventListener('DOMContentLoaded', function() {
            if (window.HSStaticMethods) {
                window.HSStaticMethods.autoInit();
            }
        });
    </script>
</body>
</html>

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-slate-200">
                <!-- Event Header with Image -->
                <div class="relative h-96 w-full bg-slate-200">
                    @if($event->image)
                        <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/20 to-transparent"></div>
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-24 h-24 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Event Header Content -->
                    <div class="absolute bottom-0 left-0 right-0 p-8 sm:p-12">
                        <!-- Title -->
                        <h1 class="text-5xl sm:text-6xl font-bold text-white tracking-tight drop-shadow-lg">
                            {{ $event->title }}
                        </h1>
                    </div>
                </div>

                <!-- Event Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-8 sm:p-12">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Description -->
                        <div class="bg-slate-50 rounded-xl p-8 border border-slate-200">
                            <h2 class="text-3xl font-bold text-slate-900 mb-6">About this Event</h2>
                            <div class="text-slate-700 leading-relaxed space-y-4 text-lg">
                                <p>{{ $event->description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-8 border border-slate-200 sticky top-24">
                            <h3 class="text-2xl font-bold text-slate-900 mb-6">Event Details</h3>
                            
                            <div class="space-y-6">
                                <!-- Date Info -->
                                <div class="pb-6 border-b border-slate-200">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Event Date</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}
                                    </p>
                                </div>

                                <!-- Time Info -->
                                <div class="pb-6 border-b border-slate-200">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Start Time</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ \Carbon\Carbon::parse($event->event_date)->format('h:i A') }}
                                    </p>
                                </div>

                                <!-- Creator Info -->
                                <div class="pb-6 border-b border-slate-200">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Organized By</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ $event->creator->name ?? 'Unknown' }}
                                    </p>
                                </div>

                                <!-- Categories -->
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Categories</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($event->categories as $category)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                                                {{ $category->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Back Button -->
                            <a href="{{ route('home') }}" class="mt-8 w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

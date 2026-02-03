<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1 class="text-5xl font-bold text-slate-900 tracking-tight mb-4">Discover Events</h1>
                <p class="text-xl text-slate-600 leading-relaxed">
                    Browse the latest conferences, workshops, and meetups in our curated collection.
                </p>
            </div>
            
            <!-- Events Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 my-24">
                @foreach ($events as $event)
                    <a href="{{ route('events.show', $event) }}" class="group bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full border border-slate-200 overflow-hidden hover:-translate-y-2">
                        <!-- Event Image -->
                        <div class="relative overflow-hidden aspect-[4/3] w-full bg-slate-100">
                            @if($event->image)
                                <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            
                            <!-- Overlay on hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Date Badge -->
                            <div class="absolute top-4 right-4 bg-blue-600 text-white px-3 py-1.5 rounded-lg shadow-lg font-semibold text-sm">
                                {{ \Carbon\Carbon::parse($event->event_date)->format('M d') }}
                            </div>
                        </div>
                        
                        <!-- Event Content -->
                        <div class="p-6 flex flex-col flex-grow">
                            <div class="mb-4">
                                <h2 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2" title="{{ $event->title }}">
                                    {{ $event->title }}
                                </h2>
                                <p class="text-slate-600 text-sm line-clamp-3 leading-relaxed">
                                    {{ $event->description }}
                                </p>
                            </div>
                            
                            <!-- Categories -->
                            <div class="mt-auto flex flex-wrap items-center gap-2 pt-4 border-t border-slate-100">
                                @foreach($event->categories as $category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-24 flex justify-center">
                <div class="space-y-4">
                    {{ $events->onEachSide(1)->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

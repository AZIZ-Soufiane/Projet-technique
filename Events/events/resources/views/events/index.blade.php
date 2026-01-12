<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-4">Discover Events</h1>
                <p class="text-lg text-slate-600 leading-relaxed">
                    Browse the latest conferences, workshops, and meetups in our curated collection.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($events as $event)
                    <a href="{{ route('events.show', $event) }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full border border-slate-100 overflow-hidden transform hover:-translate-y-1 block">
                        <div class="relative overflow-hidden aspect-[4/3] w-full">
                            @if($event->image)
                                 <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg shadow-sm border border-slate-100">
                                <span class="text-sm font-bold text-blue-600">
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('M d') }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-6 flex flex-col flex-grow">
                            <div class="mb-4">
                                <h2 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors line-clamp-1" title="{{ $event->title }}">{{ $event->title }}</h2>
                                <p class="text-slate-600 text-sm line-clamp-3 leading-relaxed">{{ $event->description }}</p>
                            </div>
                            
                            <div class="mt-auto flex flex-wrap items-center gap-2 pt-4 border-t border-slate-100">
                                @foreach($event->categories as $category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-12 flex justify-center">
                {{ $events->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
                <div class="relative h-96 w-full">
                    @if($event->image)
                        <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                    @else
                        <div class="w-full h-full bg-slate-200 flex items-center justify-center">
                            <svg class="w-24 h-24 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                    
                    <div class="absolute bottom-0 left-0 right-0 p-8 sm:p-12">
                        <div class="flex flex-wrap gap-3 mb-6">
                            @foreach($event->categories as $category)
                                <span class="px-4 py-1.5 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-md text-white border border-white/10 shadow-sm">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mb-2 drop-shadow-md">{{ $event->title }}</h1>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 p-8 sm:p-12">
                    <div class="lg:col-span-2 space-y-8">
                        <div class="prose prose-lg prose-slate max-w-none">
                            <h3 class="text-2xl font-bold text-slate-900 mb-4">About this Event</h3>
                            <div class="text-slate-600 leading-relaxed space-y-4">
                                <p>{{ $event->description }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 sticky top-24">
                            <h3 class="text-xl font-bold text-slate-900 mb-6">Event Details</h3>
                            
                            <div class="space-y-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 p-3 bg-blue-100 rounded-xl">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Date</p>
                                        <p class="text-lg font-semibold text-slate-900 mt-0.5">
                                            {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex-shrink-0 p-3 bg-blue-100 rounded-xl">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Time</p>
                                        <p class="text-lg font-semibold text-slate-900 mt-0.5">
                                            {{ \Carbon\Carbon::parse($event->event_date)->format('h:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-8 border-t border-slate-200">
                                <a href="{{ route('home') }}" class="flex items-center justify-center w-full px-6 py-3.5 bg-white border-2 border-slate-200 rounded-xl text-slate-700 font-bold hover:border-slate-300 hover:bg-slate-50 transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Back to Events
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

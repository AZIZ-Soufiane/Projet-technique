<div id="events-table-container">
    <!-- Table Card -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            {{ __('messages.event_details') }}
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            {{ __('messages.date') }}
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            {{ __('messages.status') }}
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            {{ __('messages.categories') }}
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <!-- Event Details -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $event->title }}</p>
                                    <p class="text-sm text-slate-500 truncate">{{ $event->description }}</p>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600 font-medium">
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                                </div>
                                <div class="text-sm text-slate-500">
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('h:i A') }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold 
                                    @if($event->status === 'published')
                                        bg-green-100 text-green-800
                                    @else
                                        bg-yellow-100 text-yellow-800
                                    @endif">
                                    <span class="w-2 h-2 rounded-full 
                                        @if($event->status === 'published')
                                            bg-green-600
                                        @else
                                            bg-yellow-600
                                        @endif"></span>
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>

                            <!-- Categories -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($event->categories as $category)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                                            {{ $category->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500">No categories</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <button @click="openEditModal(JSON.parse($el.dataset.event))" data-event="{{ json_encode($event) }}" class="inline-flex items-center justify-center p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="deleteEvent({{ $event->id }})" class="inline-flex items-center justify-center p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <p class="text-lg font-semibold text-slate-900 mb-1">No events found</p>
                                    <p class="text-sm text-slate-500">Get started by creating a new event.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $events->links() }}
    </div>
</div>

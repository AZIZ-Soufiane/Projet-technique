<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Manage Events</h1>
                    <p class="text-slate-500 mt-1">Create, update, and organize your events efficiently.</p>
                </div>
                <button onclick="openCreateModal()" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-600/20 transition-all duration-200 transform hover:scale-[1.02]">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Event
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 mb-8">
                <div class="flex flex-col md:flex-row gap-5">
                    <div class="w-full md:flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="search" placeholder="Search events by title..." class="pl-10 block w-full rounded-xl border border-slate-300 bg-white py-2.5 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm text-slate-900 placeholder:text-slate-400 transition-colors">
                </div>
            </div>

            <div id="table-wrapper">
                @include('admin.events.partials.table', ['events' => $events])
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="event-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-100"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100">
                        <div class="sm:flex sm:items-center justify-between">
                            <h3 class="text-xl font-bold leading-6 text-slate-900" id="modal-title">Event Details</h3>
                            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-500 transition-colors rounded-lg p-1 hover:bg-slate-50">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <form id="event-form" class="p-6 space-y-6">
                        @csrf
                        <input type="hidden" id="event-id">
                        
                        <div>
                            <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">Event Title</label>
                            <input type="text" name="title" id="title" class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" placeholder="e.g. Annual Tech Conference">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                            <textarea name="description" id="description" rows="4" class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors" placeholder="Describe the event..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="event_date" class="block text-sm font-semibold text-slate-700 mb-2">Date & Time</label>
                                <input type="datetime-local" name="event_date" id="event_date" class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                                <div class="relative">
                                    <select name="status" id="status" class="appearance-none block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm transition-colors">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Categories</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($categories as $category)
                                    <div class="relative">
                                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat_{{ $category->id }}" class="peer sr-only">
                                        <label for="cat_{{ $category->id }}" class="flex items-center justify-center px-3 py-2 text-sm font-medium text-slate-600 bg-slate-50 border-2 border-slate-200 rounded-lg cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 hover:bg-slate-100">
                                            {{ $category->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    
                        <div>
                            <label for="image" class="block text-sm font-semibold text-slate-700 mb-2">Event Image</label>
                            <div class="flex items-center justify-center w-full">
                                <label for="image" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                        <p class="mb-2 text-sm text-slate-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    </div>
                                    <input type="file" name="image" id="image" class="hidden" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </form>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
                        <button type="submit" form="event-form" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors">Save Event</button>
                        <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts remain same, just updated to support new DOM structure if needed. 
         Luckily logic is mostly ID based, which I preserved. -->
    <script>
        // Filters & Search
        const searchInput = document.getElementById('search');

        let timeoutId;

        function fetchEvents(page = 1) {
            const search = searchInput.value;
            
            fetch(`{{ route('admin.events.index') }}?page=${page}&search=${search}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('table-wrapper').innerHTML = html;
            });
        }

        // Debounce search
        searchInput.addEventListener('input', () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fetchEvents(), 300);
        });
        


        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination a')) {
                e.preventDefault();
                const url = new URL(e.target.closest('.pagination a').href);
                const page = url.searchParams.get('page');
                fetchEvents(page);
            }
        });

        // Modal Logic
        const modal = document.getElementById('event-modal');
        const form = document.getElementById('event-form');
        const modalTitle = document.getElementById('modal-title');
        const eventIdInput = document.getElementById('event-id');

        function openCreateModal() {
            form.reset();
            eventIdInput.value = '';
            modalTitle.textContent = 'Create New Event';
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }



        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const id = eventIdInput.value;
            const url = id ? `/admin/events/${id}` : '{{ route('admin.events.store') }}';
            // Note: When sending FormData with files, we must use POST.
            // Laravel handles PUT via _method field.
            
            if (id) {
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(async response => {
                const json = await response.json();
                if (response.ok) {
                    closeModal();
                    fetchEvents();
                } else {
                    alert('Error: ' + (json.message || 'Check your inputs'));
                    console.error(json);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</x-app-layout>

<x-app-layout>
    <div class="py-12" x-data="adminEvents()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-8">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 tracking-tight">{{ __('messages.manage_events') }}</h1>
                    <p class="text-slate-600 mt-2">{{ __('messages.manage_events_desc') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Language Switcher Buttons -->
                    <div class="flex gap-2 bg-white border border-slate-200 rounded-lg p-1 shadow-sm">
                        <a href="{{ url('locale/en') }}" class="px-4 py-2 rounded-md font-semibold text-sm transition {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                            ENG
                        </a>
                        <a href="{{ url('locale/fr') }}" class="px-4 py-2 rounded-md font-semibold text-sm transition {{ app()->getLocale() === 'fr' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                            FR
                        </a>
                    </div>
                    <button @click="openCreateModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg shadow-blue-600/30 transition-all duration-200 transform hover:scale-105 whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('messages.new_event') }}
                    </button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-lg p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div class="md:col-span-2 relative">
                        <label for="search" class="sr-only">Search events</label>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="search" x-model="search" @input.debounce.300ms="fetchEvents()" placeholder="Search events by title..." class="pl-10 pr-4 block w-full rounded-lg border border-slate-300 bg-white py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>

                    <!-- Category Filter -->
                    <div class="relative">
                        <label for="category-filter" class="sr-only">Filter by category</label>
                        <div class="relative">
                            <select id="category-filter" x-model="category" @change="fetchEvents()" class="appearance-none block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-3 pr-10 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Wrapper -->
            <div id="table-wrapper">
                @include('admin.events.partials.table', ['events' => $events])
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="event-modal" x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-transition>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-100"></div>

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl border border-slate-200">
                
                <!-- Modal Header -->
                <div class="bg-white px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900" id="modal-title">Event Details</h3>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors rounded-lg p-1 hover:bg-slate-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form @submit.prevent="submitForm()" class="p-6 space-y-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                    @csrf
                    
                    <!-- Title Field -->
                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">Event Title</label>
                        <input type="text" name="title" x-model="form.title" id="title" class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="e.g., Annual Tech Conference">
                    </div>

                    <!-- Description Field -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                        <textarea name="description" x-model="form.description" id="description" rows="4" class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="Describe the event..."></textarea>
                    </div>

                    <!-- Date & Status Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="event_date" class="block text-sm font-semibold text-slate-700 mb-2">Date & Time</label>
                            <input type="datetime-local" name="event_date" x-model="form.event_date" id="event_date" class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                            <div class="relative">
                                <select name="status" x-model="form.status" id="status" class="appearance-none block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 pr-10 text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Categories</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($categories as $category)
                                <div class="relative">
                                    <input type="checkbox" name="categories[]" x-model="form.categories" value="{{ $category->id }}" id="cat_{{ $category->id }}" class="peer sr-only">
                                    <label for="cat_{{ $category->id }}" class="flex items-center justify-center px-3 py-2.5 text-sm font-medium text-slate-600 bg-white border-2 border-slate-200 rounded-lg cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 hover:border-slate-300">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label for="image" class="block text-sm font-semibold text-slate-700 mb-2">Event Image</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="image" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <p class="text-sm text-slate-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                </div>
                                <input type="file" name="image" id="image" class="hidden" accept="image/*">
                            </label>
                        </div>
                    </div>
                </form>

                <!-- Modal Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-4 py-2 bg-white text-slate-700 font-semibold border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        Save Event
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adminEvents() {
            return {
                events: @json($events->items()),
                search: '',
                category: '',
                modalOpen: false,
                editingEvent: null,
                form: {
                    title: '',
                    description: '',
                    event_date: '',
                    status: 'draft',
                    categories: []
                },
                init() {
                    document.addEventListener('click', (e) => {
                        if (e.target.closest('.pagination a')) {
                            e.preventDefault();
                            const url = new URL(e.target.closest('.pagination a').href);
                            const page = url.searchParams.get('page');
                            this.fetchEvents(page);
                        }
                    });
                },
                fetchEvents(page = 1) {
                    fetch(`{{ route('admin.events.index') }}?page=${page}&search=${this.search}&category=${this.category}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('table-wrapper').innerHTML = html;
                    });
                },
                openCreateModal() {
                    this.form = {
                        title: '',
                        description: '',
                        event_date: '',
                        status: 'draft',
                        categories: []
                    };
                    this.editingEvent = null;
                    document.getElementById('modal-title').textContent = 'Create New Event';
                    this.modalOpen = true;
                },
                openEditModal(event) {
                    this.form = {
                        title: event.title,
                        description: event.description,
                        event_date: event.event_date.replace(' ', 'T'),
                        status: event.status,
                        categories: event.categories.map(c => c.id)
                    };
                    this.editingEvent = event;
                    document.getElementById('modal-title').textContent = 'Edit Event';
                    this.modalOpen = true;
                },
                closeModal() {
                    this.modalOpen = false;
                },
                submitForm() {
                    const formData = new FormData();
                    formData.append('title', this.form.title);
                    formData.append('description', this.form.description);
                    formData.append('event_date', this.form.event_date);
                    formData.append('status', this.form.status);
                    this.form.categories.forEach(cat => formData.append('categories[]', cat));
                    const imageInput = document.getElementById('image');
                    if (imageInput.files[0]) {
                        formData.append('image', imageInput.files[0]);
                    }

                    const url = this.editingEvent ? `/admin/events/${this.editingEvent.id}` : '{{ route('admin.events.store') }}';
                    const method = this.editingEvent ? 'PUT' : 'POST';

                    if (this.editingEvent) {
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
                            this.closeModal();
                            this.fetchEvents();
                        } else {
                            alert('Error: ' + (json.message || 'Check your inputs'));
                        }
                    })
                    .catch(error => console.error('Error:', error));
                },
                deleteEvent(id) {
                    if (!confirm('Are you sure you want to delete this event?')) return;

                    fetch(`/admin/events/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            this.fetchEvents();
                        } else {
                            alert('Error deleting event');
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>

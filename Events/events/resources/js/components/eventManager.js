import { baseItemManager } from './baseManager.js';

/**
 * Event Manager for Admin Panel
 * 
 * Manages CRUD operations for events including:
 * - Fetching events with search and category filters
 * - Creating and editing events
 * - Deleting events with confirmation
 * - Image upload with preview
 * - Category multi-select
 */
export default ({
    initialEvents = [],
    initialPagination = {},
    initialCategoryId = '',
    initialSearch = '',
    csrf = '',
    deleteConfirmMessage = 'Are you sure you want to delete this event?',
    createSuccessMessage = 'Event created successfully',
    updateSuccessMessage = 'Event updated successfully',
    deleteSuccessMessage = 'Event deleted successfully'
}) => ({
    // Spread item manager functionality (includes base manager)
    ...baseItemManager({
        initialItems: initialEvents,
        initialPagination,
        initialCategoryId,
        initialSearch,
        csrf
    }),

    // Map 'items' to 'events' for semantic clarity
    get events() {
        return this.items;
    },
    set events(value) {
        this.items = value;
    },

    // Event-specific properties
    deleteConfirmMessage: deleteConfirmMessage,
    createSuccessMessage: createSuccessMessage,
    updateSuccessMessage: updateSuccessMessage,
    deleteSuccessMessage: deleteSuccessMessage,

    // Event-specific form data structure (overrides base formData)
    formData: {
        id: null,
        title: '',
        description: '',
        event_date: '',
        status: 'draft',
        categories: [],
        image: null
    },
    imagePreview: null,

    // Modal state
    modalOpen: false,
    editingEvent: null,

    // Category filter (using 'category' instead of 'categoryId' to match existing implementation)
    category: '',

    init() {
        // Initialize item manager (includes base)
        this.initItemManager();

        // Setup pagination click handler
        this.$nextTick(() => {
            document.addEventListener('click', (e) => {
                const paginationLink = e.target.closest('#table-wrapper .pagination a');
                if (paginationLink) {
                    e.preventDefault();
                    const url = new URL(paginationLink.href);
                    const page = url.searchParams.get('page');
                    this.fetchEvents(parseInt(page) || 1);
                }
            });
        });

        // Watch category instead of categoryId
        this.$watch('category', () => this.fetchEvents());
    },

    // Override fetchItems from baseItemManager
    async fetchEvents(page = 1) {
        this.loading = true;
        this.currentPage = page;

        try {
            const params = new URLSearchParams({
                search: this.search,
                category: this.category,
                page: page
            });

            const response = await fetch(`/admin/events?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch events');
            }

            const html = await response.text();

            // Update the table wrapper with new HTML
            const tableWrapper = document.getElementById('table-wrapper');
            if (tableWrapper) {
                tableWrapper.innerHTML = html;
                this.reinitializeUI();
            }
        } catch (error) {
            console.error('Error fetching events:', error);
            this.notify('Error loading events', 'error');
        } finally {
            this.loading = false;
        }
    },

    // Alias for compatibility
    fetchItems(page = 1) {
        return this.fetchEvents(page);
    },

    openCreateModal() {
        this.resetForm();
        this.isEdit = false;
        this.editingEvent = null;
        this.modalOpen = true;

        // Update modal title
        const modalTitle = document.getElementById('modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Create New Event';
        }
    },

    openEditModal(event) {
        this.resetForm();
        this.isEdit = true;
        this.editingEvent = event;

        // Populate form data
        this.formData.id = event.id;
        this.formData.title = event.title;
        this.formData.description = event.description;
        this.formData.event_date = event.event_date ? event.event_date.replace(' ', 'T') : '';
        this.formData.status = event.status;
        this.formData.categories = event.categories ? event.categories.map(c => c.id) : [];

        // Handle image preview if available
        if (event.image) {
            this.imagePreview = `/storage/${event.image}`;
        }

        this.modalOpen = true;

        // Update modal title
        const modalTitle = document.getElementById('modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Edit Event';
        }
    },

    closeModal() {
        this.modalOpen = false;
        this.resetForm();
    },

    resetForm() {
        this.errors = {};
        this.imagePreview = null;
        this.formData = {
            id: null,
            title: '',
            description: '',
            event_date: '',
            status: 'draft',
            categories: [],
            image: null
        };

        // Reset file input manually if present
        const fileInput = document.getElementById('image');
        if (fileInput) {
            fileInput.value = '';
        }
    },

    handleFileUpload(event) {
        const file = event.target.files[0];
        this.formData.image = file;

        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },

    async submitForm() {
        this.loading = true;
        this.errors = {};

        const data = new FormData();
        data.append('title', this.formData.title);
        data.append('description', this.formData.description);
        data.append('event_date', this.formData.event_date);
        data.append('status', this.formData.status);

        // Append categories array
        this.formData.categories.forEach(id => {
            data.append('categories[]', id);
        });

        // Append image if present
        if (this.formData.image) {
            data.append('image', this.formData.image);
        }

        let url = '/admin/events';
        let method = 'POST';

        if (this.isEdit && this.editingEvent) {
            url = `/admin/events/${this.editingEvent.id}`;
            data.append('_method', 'PUT'); // Method spoofing for FormData
        }

        try {
            const response = await fetch(url, {
                method: 'POST', // Always POST for FormData with _method spoofing
                body: data,
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!this.handleFormResponse(response, result)) {
                if (response.status !== 422) {
                    this.notify(result.message || 'An error occurred', 'error');
                }
                return;
            }

            // Success
            this.closeModal();
            this.fetchEvents();

            const successMessage = this.isEdit ? this.updateSuccessMessage : this.createSuccessMessage;
            this.notify(result.message || successMessage, 'success');
        } catch (error) {
            console.error(error);
            this.notify('Unexpected error occurred', 'error');
        } finally {
            this.loading = false;
        }
    },

    async deleteEvent(id) {
        if (!confirm(this.deleteConfirmMessage)) return;

        this.loading = true;

        try {
            const response = await fetch(`/admin/events/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok) {
                this.fetchEvents();
                this.notify(result.message || this.deleteSuccessMessage, 'success');
            } else {
                this.notify(result.message || 'Failed to delete event', 'error');
            }
        } catch (error) {
            console.error('Error deleting event:', error);
            this.notify('Unexpected error occurred', 'error');
        } finally {
            this.loading = false;
        }
    },

    // Alias for compatibility with base manager
    deleteItem(id) {
        return this.deleteEvent(id);
    }
});

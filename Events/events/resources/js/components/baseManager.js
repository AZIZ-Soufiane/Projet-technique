/**
 * Base Manager for Alpine.js Components
 * 
 * Provides common functionality for event management components including:
 * - Loading state management
 * - Search and category filtering with watchers
 * - Icon refresh utilities
 * - UI reinitialization helpers
 */

const baseManager = (config = {}) => ({
    // Shared reactive properties
    loading: false,
    search: config.initialSearch || '',
    categoryId: config.initialCategoryId || '',

    /**
     * Initialize base functionality
     * Must be called from child component's init() method
     */
    initBase() {
        // Setup watchers for search and categoryId
        this.setupWatchers();

        // Initialize any UI libraries after DOM is ready
        this.$nextTick(() => {
            this.initUILibraries();
        });
    },

    /**
     * Setup watchers for reactive properties
     * Override this in child components to add custom watchers
     */
    setupWatchers() {
        // Default implementation - can be extended by child components
        // Watchers are typically set up in child components via $watch
    },

    /**
     * Initialize UI libraries (Preline, Lucide, etc.)
     */
    initUILibraries() {
        // Initialize Preline components if available
        if (typeof HSStaticMethods !== 'undefined') {
            HSStaticMethods.autoInit();
        }

        // Refresh Lucide icons if available
        if (window.refreshIcons) {
            window.refreshIcons();
        }
    },

    /**
     * Refresh icons after DOM updates
     */
    refreshIcons() {
        this.$nextTick(() => {
            if (window.refreshIcons) {
                window.refreshIcons();
            }
        });
    },

    /**
     * Re-initialize UI libraries after AJAX updates
     */
    reinitializeUI() {
        this.$nextTick(() => {
            this.initUILibraries();
        });
    }
});

// Default export: basic manager
export default baseManager;

/**
 * Base Item Manager for Alpine.js Components
 * 
 * Extends baseManager with CRUD-specific functionality:
 * - Item list and pagination management
 * - Modal management for create/edit
 * - Form state management
 * - Generic CRUD operations outline
 * - CSRF token handling
 */
export const baseItemManager = (config = {}) => ({
    // Spread base manager functionality (search, filter, loading, etc.)
    ...baseManager(config),

    // Item management properties
    items: config.initialItems || [],
    pagination: config.initialPagination || {},
    currentPage: (config.initialPagination?.current_page) || 1,

    // CRUD properties
    csrf: config.csrf || '',
    errors: {},
    showModal: false,
    isEdit: false,

    // Generic form data (override in child components with specific fields)
    formData: config.initialFormData || {},

    /**
     * Initialize item manager
     * Calls base init and sets up item-specific watchers
     */
    initItemManager() {
        // Initialize base functionality (search, filter, UI libraries)
        this.initBase();

        // Setup watchers for fetching items
        this.$watch('search', () => this.fetchItems());
        this.$watch('categoryId', () => this.fetchItems());
    },

    /**
     * Fetch items with pagination and filters
     * Child components MUST override this to provide endpoint and item property
     */
    async fetchItems(page = 1) {
        throw new Error('fetchItems() must be implemented by child component');
    },

    /**
     * Get pagination pages array for UI
     */
    get pages() {
        const pages = [];
        const lastPage = this.pagination.last_page || 1;
        const current = this.currentPage;

        let start = Math.max(1, current - 2);
        let end = Math.min(lastPage, current + 2);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    },

    /**
     * Change page handler
     */
    changePage(page) {
        if (page < 1 || page > this.pagination.last_page || page === this.currentPage) return;
        this.fetchItems(page);
    },

    /**
     * Open create modal
     * Resets form and sets create mode
     */
    openCreateModal() {
        this.resetForm();
        this.isEdit = false;
        this.showModal = true;
    },

    /**
     * Open edit modal
     * Child components should override to populate formData with item data
     */
    openEditModal(item) {
        this.resetForm();
        this.isEdit = true;
        this.showModal = true;
        // Child components should populate formData here
    },

    /**
     * Close modal and reset form
     */
    closeModal() {
        this.showModal = false;
        this.resetForm();
    },

    /**
     * Reset form state
     * Child components should override to reset specific form fields
     */
    resetForm() {
        this.errors = {};
        // Child components should reset formData fields here
    },

    /**
     * Submit form (create or update)
     * Child components should override to handle specific form submission
     */
    async submitForm() {
        throw new Error('submitForm() must be implemented by child component');
    },

    /**
     * Delete item
     * Child components should override to provide endpoint and logic
     */
    async deleteItem(id) {
        throw new Error('deleteItem() must be implemented by child component');
    },

    /**
     * Helper: Build fetch request with filters
     */
    buildFetchUrl(endpoint, page = 1, additionalParams = {}) {
        const params = new URLSearchParams({
            search: this.search,
            category: this.categoryId,
            page: page,
            ...additionalParams
        });
        return `${endpoint}?${params.toString()}`;
    },

    /**
     * Helper: Handle fetch response for paginated items
     */
    async handleFetchResponse(response) {
        if (!response.ok) {
            throw new Error('Failed to fetch items');
        }

        const data = await response.json();

        // Update pagination
        this.pagination = data;

        // Refresh icons after DOM update
        this.refreshIcons();

        return data;
    },

    /**
     * Helper: Handle form submission response
     */
    handleFormResponse(response, result) {
        if (!response.ok) {
            if (response.status === 422) { // Unprocessable Content client error response status code
                // Validation errors
                this.errors = result.errors || {};
            }
            return false;
        }
        return true;
    },

    /**
     * Helper: Dispatch notification event
     */
    notify(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { message, type }
        }));
    }
});

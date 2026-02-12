import Alpine from 'alpinejs'

// Import Alpine.js components
import eventManager from './components/eventManager.js'

// Make Alpine available globally
window.Alpine = Alpine

// Register components globally
window.eventManager = eventManager

Alpine.start()
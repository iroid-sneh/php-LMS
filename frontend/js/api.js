// API wrapper for easier usage

// Re-export API functions for convenience
const api = {
    // Authentication
    login: window.login,
    register: window.register,
    checkAuth: window.checkAuth,
    logout: window.logout,

    // API calls
    call: window.apiCall,

    // Utility functions
    formatDate: window.formatDate,
    formatDateTime: window.formatDateTime,
    showAlert: window.showAlert,

    // Validation functions
    validateEmail: window.validateEmail,
    validatePassword: window.validatePassword,
    validateRequired: window.validateRequired,
    isValidDate: window.isValidDate,
    isFutureDate: window.isFutureDate,
    isEndDateAfterStartDate: window.isEndDateAfterStartDate,
};

// Make api available globally
window.api = api;

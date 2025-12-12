import axios from 'axios';

window.axios = axios;

// Enable credentials (cookies) for cross-subdomain requests
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Function to get CSRF token
const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.content : null;
};

// Wait for DOM to be ready before setting CSRF token
const initializeCsrf = () => {
    const token = getCsrfToken();
    if (token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        console.log('✅ CSRF token initialized');
    } else {
        console.error('❌ CSRF token not found in meta tag - reloading page...');
        // Reload page once if CSRF token is not found
        if (!sessionStorage.getItem('csrf_reload_attempted')) {
            sessionStorage.setItem('csrf_reload_attempted', 'true');
            window.location.reload();
        }
    }
};

// Initialize immediately if DOM is ready, otherwise wait
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCsrf);
} else {
    initializeCsrf();
}

// Intercept requests to ensure CSRF token is always fresh
window.axios.interceptors.request.use(function (config) {
    const token = getCsrfToken();
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    } else {
        console.warn('⚠️ CSRF token not available for request:', config.url);
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

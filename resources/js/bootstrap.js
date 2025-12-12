import axios from 'axios';

window.axios = axios;

// Enable credentials (cookies) for cross-subdomain requests
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token from meta tag
const token = document.querySelector('meta[name="csrf-token"]');
if (token && token.content) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found in meta tag.');
}

// Intercept requests to ensure CSRF token is always fresh
window.axios.interceptors.request.use(function (config) {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token && token.content) {
        config.headers['X-CSRF-TOKEN'] = token.content;
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

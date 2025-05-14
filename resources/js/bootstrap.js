import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


const token = document.querySelector('meta[name="csrf-token"]');
if (token && token.content) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}else {
    // Optionally, you can throw or handle this error as needed
    // For debugging, log a clear message
    console.error('CSRF token not found in meta tag.');
}

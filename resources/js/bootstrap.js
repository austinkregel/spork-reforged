/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
import { createToaster } from "@meforma/vue-toaster";
import { router } from '@inertiajs/vue3';

const toaster = createToaster({ /* options */ });
window.toaster = toaster;

window.axios = axios;
window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let controller = new AbortController();

const abort = (message) => {
    controller.abort(message);
    controller = new AbortController();
}

router.on('before', () => abort('Request aborted due to new navigation.'));
router.on('cancel', () => abort('Request cancelled by user.'))

window.axios.interceptors.request.use(function (config) {
    config.signal = controller.signal;
    return config;
});

window.axios.interceptors.request.use(function (config) {
    return config;
}, function (error) {
    playSound('error');
    const status = error?.response?.status
    if (status === 401) {
        window.location = "/login"
        return Promise.reject(error);
    }
    if (status === 403) {
        toaster.error('You do not have permission to perform this action.');
    } else  if (status === 404) {
        toaster.error('The requested resource was not found.');
    } else if (status >= 500 || status === 422) {
        toaster.error(error?.response?.data?.message ?? error.message)
    }

    return Promise.reject(error);
}, {

});

import './echo';

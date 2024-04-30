import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '3aaf0e4999c14297146c',
    cluster: 'ap2',
    forceTLS: true,
});

// key: import.meta.env.VITE_REVERB_APP_KEY,
// wsHost: import.meta.env.VITE_REVERB_HOST,
// wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
// wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
// enabledTransports: ['ws', 'wss'],

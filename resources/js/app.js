import "vue-toastification/dist/index.css";

import '../css/app.css';

import './bootstrap';

import 'maz-ui/styles';

import "@/Directives/tooltip.css";

import { createInertiaApp } from '@inertiajs/vue3';

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { createPinia } from 'pinia';

import { createApp, h } from 'vue';

import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import Toast, { POSITION } from "vue-toastification";

import tooltip from "@/Directives/tooltip";

import VueApexCharts from "vue3-apexcharts";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const pinia = createPinia()

const options = {
  icon: false,
  position: POSITION.BOTTOM_RIGHT,
  toastDefaults: {
    default: {
      // timeout: false,
      closeButton: false,
      newestOnTop: true,
      draggable: false,
      hideProgressBar: true,
      toastClassName: "my_toast_body_class",
      containerClassName: 'my_toast_container_ads'
    }
  }
}

// isTyping() {
//   let channel = Echo.private('chat');

//   setTimeout(function() {
//     channel.whisper('typing', {
//       user: Laravel.user,
//         typing: true
//     });
//   }, 300);
// }

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(pinia)
      .use(VueApexCharts)
      .use(Toast, options)
      .directive("tooltip", tooltip)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});

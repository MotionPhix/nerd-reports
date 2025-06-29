import '../css/app.css';

import './bootstrap';

import 'maz-ui/styles';

import 'vue-sonner/style.css';

import { createInertiaApp } from '@inertiajs/vue3';

import { renderApp } from '@inertiaui/modal-vue'

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { createPinia } from 'pinia';

import { createApp } from 'vue';

import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import VueApexCharts from 'vue3-apexcharts';

const appName = import.meta.env.VITE_APP_NAME || 'Rapports';

const pinia = createPinia();

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: renderApp(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(pinia)
      .use(VueApexCharts)
      .mount(el);
  },
  progress: {
    color: '#4B5563'
  }
});

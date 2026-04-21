import { createApp } from 'vue';

// Third-Party Libs
import PQueue from 'p-queue';

// Init plugins

// Helpers/Filter

// Reusable Components
import ResultCard from '@/components/openaccess/Card.vue';
// dhc-components
import { LoadingSpinner } from 'dhc-components';

// Init Libs
// PQueue, httpQueue
const queue = new PQueue({concurrency: 1});
window.$httpQueue = queue;

// Components
import App from '@/components/openaccess/App.vue';

// Init required libs
// Pinia
import pinia from '@/bootstrap/store.js';
// Vue-Router
import {
    openRouter as router
} from '@/bootstrap/router.js';
// vue-i18n
import i18n from '@/bootstrap/i18n.js';
// vue-final-modal
import { createVfm } from 'vue-final-modal';
// Font Awesome
import '@/bootstrap/font.js';
// Plugin System
import { SpPS } from '@/bootstrap/plugins.js';

import initGlobalComponents from '@/bootstrap/global-components.js';

const app = createApp(App);
app.use(i18n);
app.use(router);
app.use(pinia);
app.use(createVfm());

initGlobalComponents(app);

SpPS.initialize(app, i18n.global.t);

app.component('ResultCard', ResultCard);
app.component('LoadingSpinner', LoadingSpinner);

// Mount Vue
app.mount('#app');
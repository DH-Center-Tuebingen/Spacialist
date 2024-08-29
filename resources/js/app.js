import { createApp } from 'vue';

// Third-Party Libs
import PQueue from 'p-queue';

// Init plugins
import { provideToast } from '@/plugins/toast.js';

// Helpers/Filter

// Init Libs
// PQueue, httpQueue
const queue = new PQueue({ concurrency: 1 });
window.$httpQueue = queue;

// Components
import App from '@/App.vue';

// Init required libs
// Vuex
import store from '@/bootstrap/store.js';
// Vue-Router
import router from '%router';
// Axios
import '@/bootstrap/http.js';
// vue-i18n
import i18n from '@/bootstrap/i18n.js';
// vue-final-modal
import { createVfm } from 'vue-final-modal';
// Font Awesome
import '@/bootstrap/font.js';
// Laravel Echo (Frontend part of Reverb aka WebSockets)
import '@/bootstrap/websocket.js';

// Plugin System
import { SpPS } from '@/bootstrap/plugins.js';

import initGlobalComponents from '@/bootstrap/global-components.js';
import initDirectives from '@/bootstrap/directives.js';

const app = createApp(App);
app.use(i18n);
app.use(router);
app.use(store);
// app.use(vueAuth);
app.use(createVfm());

initDirectives(app);
initGlobalComponents(app);

SpPS.initialize(app, i18n.global.t);

// Mount Vue
app.mount('#app');
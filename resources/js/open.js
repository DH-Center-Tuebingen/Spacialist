import { createApp } from 'vue';

// Third-Party Libs
import PQueue from "p-queue";

// Init plugins

// Helpers/Filter

// Reusable Components
import AttributeList from "@/components/AttributeList.vue";
import ResultCard from '@/components/openaccess/Card.vue';
import MarkdownViewer from '@/components/mde/Viewer.vue';

// Init Libs
// PQueue, httpQueue
const queue = new PQueue({concurrency: 1});
window.$httpQueue = queue;

// Third-Party Components
import Multiselect from "@vueform/multiselect";
import DatePicker from "vue-datepicker-next";
import draggable from "vuedraggable";

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
// Font Awesome
import '@/bootstrap/font.js';

const app = createApp(App);
app.use(i18n);
app.use(router);
app.use(pinia);

app.component("attribute-list", AttributeList);
app.component('result-card', ResultCard);
// Third-Party components
app.component("multiselect", Multiselect);
app.component("date-picker", DatePicker);
app.component("draggable", draggable);
app.component('md-viewer', MarkdownViewer);

// Mount Vue
app.mount("#app");
import { createApp } from 'vue';

// Third-Party Libs
import PQueue from 'p-queue';

// Init plugins

// Helpers/Filter

// Reusable Components
import Alert from  '@/components/Alert.vue';
import AttributeList from '@/components/AttributeList.vue';
import ResultCard from '@/components/openaccess/Card.vue';
import Richtext from '@/components/attribute/Richtext.vue';
import MarkdownEditor from '@/components/mde/Wrapper.vue';
import MarkdownViewer from '@/components/mde/Viewer.vue';

// Init Libs
// PQueue, httpQueue
const queue = new PQueue({concurrency: 1});
window.$httpQueue = queue;

// Third-Party Components
import Multiselect from '@vueform/multiselect';
import DatePicker from 'vue-datepicker-next';
import draggable from 'vuedraggable';

// Components
import App from '@/components/openaccess/App.vue';

// Init required libs
// Vuex
import store from '@/bootstrap/store.js';
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
app.use(store);

app.component('Alert', Alert);
app.component('AttributeList', AttributeList);
app.component('ResultCard', ResultCard);
app.component('Richtext', Richtext);
app.component('MdEditor', MarkdownEditor);
app.component('MdViewer', MarkdownViewer);
// Third-Party components
app.component('Multiselect', Multiselect);
app.component('DatePicker', DatePicker);
app.component('Draggable', draggable);

// Mount Vue
app.mount('#app');

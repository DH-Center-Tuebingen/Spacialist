import { createApp } from 'vue';

// Third-Party Libs
const {default: PQueue} = require('p-queue');
import hljs from 'highlight.js';

// Init plugins
import { provideToast } from './plugins/toast.js';

// Helpers/Filter

// Reusable Components
import AttributeList from './components/AttributeList.vue';
import EntityTypeList from './components/EntityTypeList.vue';
import EntityTree from './components/EntityTree.vue';
import EntityBreadcrumbs from './components/EntityBreadcrumbs.vue';
import UserAvatar from './components/UserAvatar.vue';
import ActivityLog from './components/ActivityLog.vue';
import CommentList from './components/CommentList.vue';
import EmojiPicker from './components/EmojiPicker.vue';
import GlobalSearch from './components/search/Global.vue';
import SimpleSearch from './components/search/Simple.vue';
import Alert from './components/Alert.vue';

// Init Libs
// PQueue, httpQueue
const queue = new PQueue({concurrency: 1});
window.$httpQueue = queue;

// Third-Party Components
import Multiselect from '@vueform/multiselect';
import VueUploadComponent from 'vue-upload-component';
import DatePicker from 'vue2-datepicker';
import InfiniteLoading from 'vue-infinite-loading';
import draggable from 'vuedraggable';
import { Tree, Node } from "tree-vue-component";
import VueFinalModal from 'vue-final-modal';

// Components
import App from './App.vue';

// Init required libs
// Vuex
import store from './bootstrap/store.js';
// Vue-Router
import router from './bootstrap/router.js';
// Axios
import './bootstrap/http.js';
// Vue-Auth
import vueAuth from './bootstrap/auth.js';
// vue-i18n
import i18n from './bootstrap/i18n.js';
// Font Awesome
import './bootstrap/font.js';
import { can } from './helpers/helpers.js';

const app = createApp(App);
app.use(i18n);
app.use(router);
app.use(store);
app.use(vueAuth);
app.use(VueFinalModal());
app.use(provideToast());

// Directives
app.directive('dcan', {
    terminal: true,
    beforeMount(el, bindings) {
        const canI = can(bindings.value, bindings.modifiers.one);
        
        if(!canI) {
            this.warning = document.createElement('p');
            this.warning.className = 'alert alert-warning v-can-warning';
            this.warning.innerHTML = 'You do not have permission to access this page';
            for(let i=0; i<el.children.length; i++) {
                let c = el.children[i];
                c.classList.add('v-can-hidden');
            }
            el.appendChild(this.warning);
        }
    },
    unmounted(el) {
        if(!el.children) return;
        for(let i=0; i<el.children.length; i++) {
            let c = el.children[i];
            // remove our warning elem
            if(c.classList.contains('v-can-warning')) {
                el.removeChild(c);
                continue;
            }
            if(c.classList.contains('v-can-hidden')) {
                c.classList.remove('v-can-hidden');
            }
        }
    }
});
app.directive('highlightjs', {
  deep: true,
  beforeMount(el, binding) {
    // on first bind, highlight all targets
    let targets = el.querySelectorAll('code')
    targets.forEach((target) => {
      // if a value is directly assigned to the directive, use this
      // instead of the element content.
      if (binding.value) {
        target.innerHTML = binding.value
      }
      hljs.highlightBlock(target)
    })
  },
  updated(el, binding) {
    // after an update, re-fill the content and then highlight
    let targets = el.querySelectorAll('code')
    targets.forEach((target) => {
      if (binding.value) {
        target.innerHTML = binding.value
        hljs.highlightBlock(target)
      }
    })
  }
})

// Components
app.component('attribute-list', AttributeList);
app.component('entity-type-list', EntityTypeList);
app.component('entity-tree', EntityTree);
app.component('entity-breadcrumbs', EntityBreadcrumbs);
app.component('user-avatar', UserAvatar);
app.component('activity-log', ActivityLog);
app.component('comment-list', CommentList);
app.component('emoji-picker', EmojiPicker);
app.component('global-search', GlobalSearch);
app.component('simple-search', SimpleSearch);
app.component('alert', Alert);
// Third-Party components
app.component('multiselect', Multiselect);
app.component('file-upload', VueUploadComponent);
app.component('date-picker', DatePicker);
app.component('infinite-loading', InfiniteLoading);
app.component('draggable', draggable);
app.component('node', Node);
app.component('tree', Tree);

// Mount Vue
app.mount('#app');
import { createApp } from 'vue';

// Third-Party Libs
import PQueue from 'p-queue';
import hljs from 'highlight.js';

// Init plugins
import { provideToast } from '@/plugins/toast.js';

// Helpers/Filter

// Reusable Components
import AttributeList from '@/components/AttributeList.vue';
import EntityTypeList from '@/components/EntityTypeList.vue';
import EntityTree from '@/components/tree/Entity.vue';
import EntityBreadcrumbs from '@/components/EntityBreadcrumbs.vue';
import UserAvatar from '@/components/UserAvatar.vue';
import ActivityLog from '@/components/ActivityLog.vue';
import CommentList from '@/components/CommentList.vue';
import EmojiPicker from '@/components/EmojiPicker.vue';
import GlobalSearch from '@/components/search/Global.vue';
import SimpleSearch from '@/components/search/Simple.vue';
import InteractiveMap from '@/components/map/InteractiveMap.vue';
import Alert from '@/components/Alert.vue';
import NotificationBody from '@/components/notification/NotificationBody.vue';
import CsvTable from '@/components/CsvTable.vue';
import Gradient from '@/components/Gradient.vue';
import MarkdownViewer from '@/components/mde/Viewer.vue';
import MarkdownEditor from '@/components/mde/Wrapper.vue';
import BibtexCode from '@/components/bibliography/BibtexCode.vue';

// Init Libs
// PQueue, httpQueue
const queue = new PQueue({concurrency: 1});
window.$httpQueue = queue;

// Third-Party Components
import Multiselect from '@vueform/multiselect';
import VueUploadComponent from 'vue-upload-component';
import DatePicker from 'vue-datepicker-next';
import draggable from 'vuedraggable';
import { Tree, Node, } from "tree-vue-component";

// Components
import App from '@/App.vue';

// Init required libs
// Vuex
import store from '@/bootstrap/store.js';
// Vue-Router
import router from '@/bootstrap/router.js';
// Axios
import '@/bootstrap/http.js';
// Vue-Auth
import vueAuth from '@/bootstrap/auth.js';
// vue-i18n
import i18n from '@/bootstrap/i18n.js';
// vue-final-modal
import { createVfm } from 'vue-final-modal';
// Font Awesome
import '@/bootstrap/font.js';

import {
  can,
  _debounce,
  getElementAttribute,
} from '@/helpers/helpers.js';
// Plugin System
import { SpPS } from '@/bootstrap/plugins.js';

const app = createApp(App);
app.use(i18n);
app.use(router);
app.use(store);
app.use(vueAuth);
app.use(createVfm());

// Directives
app.directive('dcan', {
    terminal: true,
    beforeMount(el, bindings) {
        const canI = can(bindings.value, bindings.modifiers.one);
        
        if(!canI) {
            const warningElem = document.createElement('p');
            warningElem.className = 'alert alert-warning v-can-warning';
            warningElem.innerHTML = i18n.global.t('main.app.page_access_denied', {perm: bindings.value});
            for(let i=0; i<el.children.length; i++) {
                let c = el.children[i];
                c.classList.add('v-can-hidden');
            }
            el.appendChild(warningElem);
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
    if(!binding.value) return;
    // on first bind, highlight all targets
    let targets = el.querySelectorAll('code');
    targets.forEach((target) => {
      // if a value is directly assigned to the directive, use this
      // instead of the element content.
      if(binding.value) {
        target.innerHTML = binding.value;
      }
      hljs.highlightElement(target);
    });
  },
  updated(el, binding) {
    if(!binding.value) return;
    // after an update, re-fill the content and then highlight
    let targets = el.querySelectorAll('code');
    targets.forEach((target) => {
      if(binding.value) {
        target.innerHTML = binding.value;
        hljs.highlightElement(target);
      }
    });
  }
});
app.directive('resize', {
  beforeMount(el, binding) {
    if(!binding.value) return;
    
    const resizeCallback = binding.value;
    window.addEventListener('resize', () => {
      const height = document.documentElement.clientHeight;
      const width = document.documentElement.clientWidth;
      resizeCallback({
        height: height,
        width: width,
      });
    });
  },
  updated(el, binding) {
    if(!binding.value) return;
    // after an update, re-fill the content and then highlight
    let targets = el.querySelectorAll('code');
    targets.forEach((target) => {
      if(binding.value) {
        target.innerHTML = binding.value;
        hljs.highlightElement(target);
      }
    });
  }
});
app.directive('infinite-scroll', {
  mounted(el, binding) {
    const options = {
      disabled: false,
      delay: 200,
      offset: 0,
    };

    const disabled = !!getElementAttribute(el, 'infinite-scroll-disabled', options.disabled, 'bool');
    const delay = getElementAttribute(el, 'infinite-scroll-delay', options.delay, 'int');
    const offset = getElementAttribute(el, 'infinite-scroll-offset', options.offset, 'int');

    if(!disabled) {
      el.onscroll = _debounce(_ => {
        const position = el.clientHeight + el.scrollTop;
        const threshold = el.scrollHeight - offset;

        if(position >= threshold) {
          binding.value();
        }
      }, delay);
    }
  },
  updated(el, binding) {
    const options = {
      disabled: false,
      delay: 200,
      offset: 0,
    };

    const disabled = !!getElementAttribute(el, 'infinite-scroll-disabled', options.disabled, 'bool');

    if(disabled) {
      el.onscroll = null;
    } else if(!disabled && !el.onscroll) {
      const delay = getElementAttribute(el, 'infinite-scroll-delay', options.delay, 'int');
      const offset = getElementAttribute(el, 'infinite-scroll-offset', options.offset, 'int');

      el.onscroll = _debounce(_ => {
        const position = el.clientHeight + el.scrollTop;
        const threshold = el.scrollHeight - offset;

        if(position >= threshold) {
          binding.value();
        }
      }, delay);
    }
  },
  beforeUnmount(el, binding) {
      el.onscroll = null;
  }
});

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
app.component('sp-map', InteractiveMap);
app.component('alert', Alert);
app.component('notification-body', NotificationBody);
app.component('csv-table', CsvTable);
app.component('color-gradient', Gradient);
app.component('md-viewer', MarkdownViewer);
app.component('md-editor', MarkdownEditor);
app.component("bibtex-code", BibtexCode);
// Third-Party components
app.component('Multiselect', Multiselect);
app.component('FileUpload', VueUploadComponent);
app.component('DatePicker', DatePicker);
app.component('Draggable', draggable);
app.component('Node', Node);
app.component('Tree', Tree);

SpPS.initialize(app, i18n.global.t);

// Mount Vue
app.mount('#app');
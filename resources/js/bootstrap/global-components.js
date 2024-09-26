import ActivityLog from '@/components/ActivityLog.vue';
import Alert from '@/components/Alert.vue';
import Attribute from '@/components/attribute/Attribute.vue';
import AttributeList from '@/components/AttributeList.vue';
import BibtexCode from '@/components/bibliography/BibtexCode.vue';
import CommentList from '@/components/CommentList.vue';
import CsvTable from '@/components/CsvTable.vue';
import EmojiPicker from '@/components/EmojiPicker.vue';
import EntityBreadcrumbs from '@/components/EntityBreadcrumbs.vue';
import EntityDetail from '../components/EntityDetail.vue';
import EntityTree from '@/components/tree/Entity.vue';
import EntityTypeList from '@/components/EntityTypeList.vue';
import GlobalSearch from '@/components/search/Global.vue';
import Gradient from '@/components/Gradient.vue';
import InteractiveMap from '@/components/map/InteractiveMap.vue';
import MarkdownEditor from '@/components/mde/Wrapper.vue';
import MarkdownViewer from '@/components/mde/Viewer.vue';
import NotificationBody from '@/components/notification/NotificationBody.vue';
import Richtext from '@/components/attribute/Richtext.vue';
import SimpleSearch from '@/components/search/Simple.vue';
import UserAvatar from '@/components/UserAvatar.vue';

// Third-Party Components
import DatePicker from 'vue-datepicker-next';
import draggable from 'vuedraggable';
import Multiselect from '@vueform/multiselect';
import VueUploadComponent from 'vue-upload-component';
import { Tree, Node, } from 'tree-vue-component';


export default function initGlobalComponents(app) {
    // Components
    app.component('ActivityLog', ActivityLog);
    app.component('Alert', Alert);
    app.component('Attribute', Attribute);
    app.component('AttributeList', AttributeList);
    app.component('BibtexCode', BibtexCode);
    app.component('ColorGradient', Gradient);
    app.component('CommentList', CommentList);
    app.component('CsvTable', CsvTable);
    app.component('EmojiPicker', EmojiPicker);
    app.component('EntityBreadcrumbs', EntityBreadcrumbs);
    app.component('EntityDetail', EntityDetail);
    app.component('EntityTree', EntityTree);
    app.component('EntityTypeList', EntityTypeList);
    app.component('GlobalSearch', GlobalSearch);
    app.component('MdEditor', MarkdownEditor);
    app.component('MdViewer', MarkdownViewer);
    app.component('NotificationBody', NotificationBody);
    app.component('Richtext', Richtext);
    app.component('SimpleSearch', SimpleSearch);
    app.component('SpMap', InteractiveMap);
    app.component('UserAvatar', UserAvatar);
    // Third-Party components
    app.component('DatePicker', DatePicker);
    app.component('Draggable', draggable);
    app.component('FileUpload', VueUploadComponent);
    app.component('Multiselect', Multiselect);
    app.component('Node', Node);
    app.component('Tree', Tree);
}

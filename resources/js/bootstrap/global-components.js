import AttributeList from '@/components/AttributeList.vue';
import Attribute from '@/components/attribute/Attribute.vue';
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
import Richtext from '@/components/attribute/Richtext.vue';
import MarkdownViewer from '@/components/mde/Viewer.vue';
import MarkdownEditor from '@/components/mde/Wrapper.vue';
import BibtexCode from '@/components/bibliography/BibtexCode.vue';
// Third-Party Components
import Multiselect from '@vueform/multiselect';
import VueUploadComponent from 'vue-upload-component';
import DatePicker from 'vue-datepicker-next';
import draggable from 'vuedraggable';
import { Tree, Node, } from 'tree-vue-component';
import EntityDetail from '../components/EntityDetail.vue';


export default function initGlobalComponents(app) {
    // Components
    app.component('AttributeList', AttributeList);
    app.component('Attribute', Attribute);
    app.component('EntityTypeList', EntityTypeList);
    app.component('EntityTree', EntityTree);
    app.component('EntityBreadcrumbs', EntityBreadcrumbs);
    app.component('UserAvatar', UserAvatar);
    app.component('ActivityLog', ActivityLog);
    app.component('CommentList', CommentList);
    app.component('EmojiPicker', EmojiPicker);
    app.component('GlobalSearch', GlobalSearch);
    app.component('SimpleSearch', SimpleSearch);
    app.component('SpMap', InteractiveMap);
    app.component('Alert', Alert);
    app.component('NotificationBody', NotificationBody);
    app.component('CsvTable', CsvTable);
    app.component('ColorGradient', Gradient);
    app.component('Richtext', Richtext);
    app.component('MdViewer', MarkdownViewer);
    app.component('MdEditor', MarkdownEditor);
    app.component('BibtexCode', BibtexCode);
    app.component('EntityDetail', EntityDetail);
    // Third-Party components
    app.component('Multiselect', Multiselect);
    app.component('FileUpload', VueUploadComponent);
    app.component('DatePicker', DatePicker);
    app.component('Draggable', draggable);
    app.component('Node', Node);
    app.component('Tree', Tree);
}

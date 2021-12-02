// Sans serif
import "@fontsource/raleway/300.css";
import "@fontsource/raleway/400.css";
import "@fontsource/raleway/600.css";
import "@fontsource/raleway/700.css";

// Monospace
import "@fontsource/source-code-pro/400.css";
import "@fontsource/source-code-pro/500.css";

// Font Awesome
import { library, dom, findIconDefinition } from '@fortawesome/fontawesome-svg-core';
import {
    faFacebookSquare,
    faGithub,
    faHtml5,
    faLaravel,
    faOrcid,
    faVuejs
} from '@fortawesome/free-brands-svg-icons';
import {
    faCircle as faCircleReg,
    faClipboard as faClipboardReg,
    faQuestionCircle,
    faLaugh,
    faSadCry,
} from '@fortawesome/free-regular-svg-icons';
import {
    faAdjust,
    faAngleDoubleLeft,
    faAngleDoubleRight,
    faAngleDown,
    faAngleLeft,
    faAngleRight,
    faAngleUp,
    faBan,
    faBell,
    faBinoculars,
    faBolt,
    faBook,
    faBookmark,
    faCalculator,
    faCalendarAlt,
    faCamera,
    faCaretDown,
    faCaretUp,
    faChartBar,
    faChartPie,
    faCheck,
    faCheckCircle,
    faCircle,
    faClipboard,
    faClock,
    faClone,
    faCog,
    faCogs,
    faComment,
    faComments,
    faCopy,
    faCopyright,
    faCubes,
    faDatabase,
    faDotCircle,
    faDownload,
    faDrawPolygon,
    faEdit,
    faEllipsisH,
    faEnvelope,
    faExchangeAlt,
    faExclamation,
    faExclamationCircle,
    faExclamationTriangle,
    faExpand,
    faExternalLinkAlt,
    faEye,
    faEyeSlash,
    faFile,
    faFileAlt,
    faFileArchive,
    faFileAudio,
    faFileCode,
    faFileDownload,
    faFileExcel,
    faFileExport,
    faFileImport,
    faFileMedicalAlt,
    faFilePdf,
    faFilePowerpoint,
    faFileUpload,
    faFileVideo,
    faFileWord,
    faFilter,
    faFolder,
    faGlobeAfrica,
    faI,
    faIdBadge,
    faIndent,
    faInfo,
    faInfoCircle,
    faLayerGroup,
    faLightbulb,
    faLink,
    faList,
    faLongArrowAltDown,
    faLongArrowAltLeft,
    faLongArrowAltRight,
    faLongArrowAltUp,
    faMagic,
    faMapMarkedAlt,
    faMapMarkerAlt,
    faMicrochip,
    faMinus,
    faMobileAlt,
    faMonument,
    faO,
    faPalette,
    faPaperPlane,
    faPaste,
    faPause,
    faPaw,
    faPlay,
    faPlus,
    faPrint,
    faPuzzlePiece,
    faQuestion,
    faRedo,
    faRepeat,
    faReply,
    faRoad,
    faRuler,
    faRulerCombined,
    faS,
    faSave,
    faSearch,
    faSearchPlus,
    faShieldAlt,
    faSignOutAlt,
    faSitemap,
    faSlidersH,
    faSort,
    faSortAlphaDown,
    faSortAlphaUp,
    faSortAmountDown,
    faSortAmountUp,
    faSortDown,
    faSortNumericDown,
    faSortNumericUp,
    faSortUp,
    faSpinner,
    faStop,
    faStopwatch,
    faSun,
    faSync,
    faTable,
    faTag,
    faTags,
    faTasks,
    faTh,
    faTimes,
    faTrash,
    faTrashRestore,
    faUnderline,
    faUndo,
    faUndoAlt,
    faUnlink,
    faUnlockAlt,
    faUpload,
    faUser,
    faUserClock,
    faUserCheck,
    faUserCog,
    faUserEdit,
    faUsers,
    faUserTimes,
    faVolumeMute,
    faVolumeUp,
    faWindowMaximize,
} from '@fortawesome/free-solid-svg-icons';

library.add(
    faFacebookSquare,
    faGithub,
    faHtml5,
    faLaravel,
    faOrcid,
    faVuejs,
    faClipboardReg,
    faQuestionCircle,
    faAdjust,
    faAngleDoubleLeft,
    faAngleDoubleRight,
    faAngleDown,
    faAngleLeft,
    faAngleRight,
    faAngleUp,
    faBan,
    faBell,
    faBinoculars,
    faBolt,
    faBook,
    faBookmark,
    faCalculator,
    faCalendarAlt,
    faCamera,
    faCaretDown,
    faCaretUp,
    faChartBar,
    faChartPie,
    faCheck,
    faCheckCircle,
    faCircle,
    faCircleReg,
    faClipboard,
    faClock,
    faClone,
    faCog,
    faCogs,
    faComment,
    faComments,
    faCopy,
    faCopyright,
    faCubes,
    faDatabase,
    faDotCircle,
    faDownload,
    faDrawPolygon,
    faEdit,
    faEllipsisH,
    faEnvelope,
    faExchangeAlt,
    faExclamation,
    faExclamationCircle,
    faExclamationTriangle,
    faExpand,
    faExternalLinkAlt,
    faEye,
    faEyeSlash,
    faFile,
    faFileAlt,
    faFileArchive,
    faFileAudio,
    faFileCode,
    faFileDownload,
    faFileExcel,
    faFileExport,
    faFileImport,
    faFileMedicalAlt,
    faFilePdf,
    faFilePowerpoint,
    faFileUpload,
    faFileVideo,
    faFileWord,
    faFilter,
    faFolder,
    faGlobeAfrica,
    faI,
    faIdBadge,
    faIndent,
    faInfo,
    faInfoCircle,
    faLaugh,
    faLayerGroup,
    faLightbulb,
    faLink,
    faList,
    faLongArrowAltDown,
    faLongArrowAltLeft,
    faLongArrowAltRight,
    faLongArrowAltUp,
    faMagic,
    faMapMarkedAlt,
    faMapMarkerAlt,
    faMicrochip,
    faMinus,
    faMobileAlt,
    faMonument,
    faO,
    faPalette,
    faPaperPlane,
    faPaste,
    faPause,
    faPaw,
    faPlay,
    faPlus,
    faPrint,
    faPuzzlePiece,
    faQuestion,
    faRedo,
    faRepeat,
    faReply,
    faRoad,
    faRuler,
    faRulerCombined,
    faS,
    faSadCry,
    faSave,
    faSearch,
    faSearchPlus,
    faShieldAlt,
    faSignOutAlt,
    faSitemap,
    faSlidersH,
    faSort,
    faSortAlphaDown,
    faSortAlphaUp,
    faSortAmountDown,
    faSortAmountUp,
    faSortDown,
    faSortNumericDown,
    faSortNumericUp,
    faSortUp,
    faSpinner,
    faStop,
    faStopwatch,
    faSun,
    faSync,
    faTable,
    faTag,
    faTags,
    faTasks,
    faTh,
    faTimes,
    faTrash,
    faTrashRestore,
    faUnderline,
    faUndo,
    faUndoAlt,
    faUnlink,
    faUnlockAlt,
    faUpload,
    faUser,
    faUserClock,
    faUserCheck,
    faUserCog,
    faUserEdit,
    faUsers,
    faUserTimes,
    faVolumeMute,
    faVolumeUp,
    faWindowMaximize,
)
dom.watch();

export const iconList = fw => {
    let list = [];
    for(let k in library.definitions) {
        const set = library.definitions[k];
        const addedCodes = {};
        for(let l in set) {
            const icon = set[l];
            const iconCode = icon[3];
            const uniqueCode = `${k}_${iconCode}`;
            const def = findIconDefinition({prefix: k, iconName: l});
            if(!addedCodes[uniqueCode]) {
                addedCodes[uniqueCode] = true;
                let str = `${k} fa-${def.iconName}`;
                if(fw) str += ` fa-fw`;
                list.push({
                    class: str,
                    key: def.iconName,
                    unicode: def.icon[3].padStart(4, "0"),
                    label: def.iconName,
                });
            }
        }
    }
    return list;
};
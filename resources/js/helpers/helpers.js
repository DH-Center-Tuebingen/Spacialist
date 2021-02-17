import auth from '../bootstrap/auth.js';
import store from '../bootstrap/store.js';

import {
    fetchAttributes,
    fetchBibliography,
    fetchTopEntities,
    fetchPreData,
    fetchUsers,
    fetchVersion,
} from '../api.js';

export async function initApp(locale) {
    await fetchPreData(locale);
    await fetchAttributes();
    await fetchUsers();
    await fetchTopEntities();
    await fetchBibliography();
    await fetchVersion();
    return new Promise(r => r(null));
}

export function can(permissionString, oneOf) {
    oneOf = oneOf || false;
    const user = store.getters.user;
    if(!user) return false;
    const permissions = permissionString.split('|');
    const hasPermission = permission => {
        return user.permissions[permission] === 1;
    };

    if(oneOf) {
        return permissions.some(hasPermission);
    } else {
        return permissions.every(hasPermission);
    }
}

export function getErrorMessages(error, suffix = '') {
    let msgObject = {};
    const r = error.response;
    if(r.status == 422) {
        if(r.data.errors) {
            for(let k in r.data.errors) {
                msgObject[`${k}${suffix}`] = r.data.errors[k];
            }
        }
    } else if(r.status == 400) {
        msgObject.global = r.data.error;
    }
    return msgObject;
}

export function rgb2hex(rgb) {
    let colors = rgb.substring(1);
    let r = parseInt(colors.substring(0, 2), 16);
    let g = parseInt(colors.substring(2, 4), 16);
    let b = parseInt(colors.substring(4, 6), 16);
    return [r, g, b];
}

export function getCertaintyClass(certainty, prefix = 'bg') {
    let classes = {};

    if(certainty <= 25) {
        classes[`${prefix}-danger`] = true;
    } else if(certainty <= 50) {
        classes[`${prefix}-warning`] = true;
    } else if(certainty <= 75) {
        classes[`${prefix}-info`] = true;
    } else {
        classes[`${prefix}-success`] = true;
    }

    return classes;
}

export function getTs() {
    const d = new Date();
    return d.getTime();
}

export function hasConcept(url) {
    if(!url) return false;
    return !!store.getters.concepts[url];
}

export function translateLabel(element, prop) {
    const value = element[prop];
    if(!value) return element;
    return translateConcept(value);
}

export function translateConcept(url) {
    const concepts = store.getters.concepts;
    if(!url || !concepts) return url;
    if(!concepts[url]) return url;
    return concepts[url].label;
}

export function translateEntityType(id) {
    return translateConcept(getEntityType(id).thesaurus_url);
}

export function getEntityType(id) {
    if(!id) return {};
    return getEntityTypes()[id];
}

export function getEntityTypes() {
    return store.getters.entityTypes;
}

export function getEntityTypeAttributes(id) {
    if(!id) return [];
    return store.getters.entityTypeAttributes(id);
}

export function getEntityTypeAttributeSelections(id) {
    const attrs = getEntityTypeAttributes(id);
    if(!attrs) return {};
    const attrIds = attrs.map(a => a.id);
    const sel = store.getters.attributeSelections;
    console.log(attrIds, "attrIds");
    console.log(sel, "sel");
    let filteredSel = {};
    for(let k in sel) {
        if(attrIds.findIndex(aid => aid == k) > -1) {
            filteredSel[k] = sel[k];
        }
    }
    console.log(filteredSel, "filteredSel");
    return filteredSel;
}

export function getEntityTypeDependencies(id) {
    console.log(store.getters.attributeDependencies, "deps");
    return {};
}

export function defaultAttributeValue(datatype) {
    const val = {};
    switch(datatype) {
        case 'dimension':
        case 'epoch':
        case 'timeperiod':
            val.value = {};
            break;
        case 'string-mc':
        case 'table':
        case 'list':
            val.value = [];
            break;
        default:
            val.value = '';
            break;
    }
    return val;
}

// Fills non-present attribute values to be used in draggable components (e.g. attribute-list)
export function fillEntityData(data, etid) {
    const attrs = getEntityTypeAttributes(etid);
    for(let i=0; i<attrs.length; i++) {
        const currAttr = attrs[i];
        if(!data[currAttr.id]) {
            data[currAttr.id] = defaultAttributeValue(currAttr.datatype);
        }
    }
    return data;
}

// Formula based on https://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color/3943023#3943023
export function getEntityColors(id, alpha = 0.5) {
    const et = getEntityType(id);
    if(!et || !et.layer) return {};
    let r, g, b, a;
    [r, g, b] = rgb2hex(et.layer.color);
    const cs = [r, g, b].map(c => {
        c /= 255.0;
        if(c <= 0.03928) c /= 12.92;
        else c = Math.pow(((c+0.055)/1.055), 2.4);
        return c;
    });
    // let cont = r*0.299 + g*0.587 + b*0.114;
    const l = cs[0]*0.2126 + cs[1]*0.7152 + cs[2]*0.0722;

    // const textColor = cont > 150 ? '#000000' : '#ffffff';
    const textColor = l > 0.179 ? '#000000' : '#ffffff';
    const color = `rgba(${r}, ${g}, ${b}, ${alpha})`;
    return {
        color: textColor,
        backgroundColor: color
    };
}

export function isLoggedIn() {
    return auth.check();
}

export function getUser() {
    return isLoggedIn() ? auth.user() : {};
}

export function userId() {
    return getUser().id || -1;
};

export function getUserBy(value, attr = 'id') {
    if(isLoggedIn()) {
        const isNum = !isNaN(value);
        const lValue = isNum ? value : value.toLowerCase();
        if(attr == 'id' && value == userId()) {
            return getUser();
        } else {
            return getUsers().find(u => isNum ? (u[attr] == lValue) : (u[attr].toLowerCase() == lValue));
        }
    } else {
        return null;
    }
};

export function getUsers() {
    return isLoggedIn() ? store.getters.users : [];
};

export function throwError(error) {
    if (error.response) {
        const r = error.response;
        const req = {
            status: r.status,
            url: r.config.url,
            method: r.config.method.toUpperCase()
        };
        this.$showErrorModal(r.data, r.headers, req);
    } else if (error.request) {
        this.$showErrorModal(error.request);
    } else {
        this.$showErrorModal(error.message || error);
    }
};

export const _cloneDeep = require('lodash/cloneDeep');
export const _debounce = require('lodash/debounce');
export const _orderBy = require('lodash/orderBy');

// UNVERIFIED

export function simpleResourceType(resource) {
    switch(resource) {
        case 'App\\Entity':
            return 'entity';
        case 'App\\Attribute':
            return 'attribute';
        case 'App\\AttributeValue':
        case 'attribute_values':
            return 'attribute_value';
        default:
            return resource;
    }
};

export function getNotificationSourceLink(notification) {
    const query = this.$route.query;
    switch(simpleResourceType(notification.data.resource.type)) {
        case 'entity':
            return {
                name: 'entitydetail',
                params: {
                    id: notification.data.resource.id
                },
                query: {
                    ...query,
                    view: 'comments',
                }
            }
        case 'attribute_value':
            return {
                name: 'entityrefs',
                params: {
                    id: notification.data.resource.meta.entity_id,
                    aid: notification.data.resource.meta.attribute_id,
                },
                query: query
            }
        default:
            return null;
    }
};

export function postComment(content, resource, replyToId = null, metadata = null, onFinish = null) {
    let data = {
        content: content,
        resource_id: resource.id,
        resource_type: simpleResourceType(resource.type),
    };
    if(replyToId) {
        data.reply_to = replyToId;
    }
    if(metadata) {
        data.metadata = metadata;
    }
    $httpQueue.add(() => axios.post(`/comment`, data).then(response => {
        if(onFinish) {
            onFinish(response.data);
        }
    }));
};

export function userNotifications() {
    return getUser().notifications || [];
};

export function markAsRead(id, from = $userNotifications()) {
    const elem = from.find(elem => elem.id === id)
    if(elem) {
        return $httpQueue.add(() => axios.patch(`notification/read/${id}`).then(response => {
            elem.read_at = Date();
        }));
    }
}

export function markAllAsRead(ids = null, from = userNotifications()) {
    if(!ids) {
        ids = from.map(elem => elem.id);
    }
    const data = {
        ids: ids
    };
    return $httpQueue.add(() => axios.patch(`notification/read/`, data).then(response => {
        let idsC = _clone(ids);
        from.forEach(elem => {
            const idx = idsC.findIndex(id => id === elem.id);
            if(idx > -1) {
                elem.read_at = Date();
                idsC.splice(idx, 1);
            }
        });
    }));
}

export function deleteNotification(id, from = userNotifications()) {
    return $httpQueue.add(() => axios.delete(`notification/${id}`).then(response => {
        const idx = from.findIndex(elem => elem.id === id);
        if(idx > -1) {
            from.splice(idx, 1);
        }
    }));
}

export function deleteAllNotifications(ids = null, from = userNotifications()) {
    if(!ids) {
        ids = from.map(elem => elem.id);
    }
    const data = {
        ids: ids
    };
    return $httpQueue.add(() => axios.patch(`notification/`, data).then(response => {
        ids.forEach(id => {
            const idx = from.findIndex(elem => elem.id === id);
            if(idx > -1) {
                from.splice(idx, 1);
            }
        });
    }));
}

export async function asyncFor(arr, callback) {
    for(let i=0; i<arr.length; i++) {
        await callback(arr[i]);
    }
};

export function showToast(title, text, type, duration) {
    type = type || 'info'; // success, info, warn, error
    duration = duration || 2000;
    this.$notify({
        group: 'spacialist',
        title: title,
        text: text,
        type: type,
        duration: duration
    });
};

export function getValidClass(msgObject, field) {
    let isInvalid = false;
    field.split('|').forEach(f => {
        if(!!msgObject[f]) {
            isInvalid = true;
        }
    });

    return {
        // 'is-valid': !msgObject[field],
        'is-invalid': isInvalid
    };
}

export function showErrorModal(errorMsg, headers, request) {
    this.$modal.show('error-modal', {msg: errorMsg, headers: headers, request: request});
};

export function slugify(s, delimiter = '-') {
	var char_map = {
		// Latin
		'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C', 
		'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I', 
		'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Å': 'O', 
		'Ø': 'O', 'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Å°': 'U', 'Ý': 'Y', 'Þ': 'TH', 
		'ß': 'ss', 
		'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c', 
		'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i', 
		'ð': 'd', 'ñ': 'n', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'Å': 'o', 
		'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u', 'Å±': 'u', 'ý': 'y', 'þ': 'th', 
		'ÿ': 'y',

		// Latin symbols
		'©': '(c)',

		// Greek
		'Α': 'A', 'Β': 'B', 'Γ': 'G', 'Δ': 'D', 'Ε': 'E', 'Ζ': 'Z', 'Η': 'H', 'Θ': '8',
		'Ι': 'I', 'Κ': 'K', 'Λ': 'L', 'Μ': 'M', 'Ν': 'N', 'Ξ': '3', 'Ο': 'O', 'Π': 'P',
		'Ρ': 'R', 'Σ': 'S', 'Τ': 'T', 'Υ': 'Y', 'Φ': 'F', 'Χ': 'X', 'Ψ': 'PS', 'Ω': 'W',
		'Î': 'A', 'Î': 'E', 'Î': 'I', 'Î': 'O', 'Î': 'Y', 'Î': 'H', 'Î': 'W', 'Îª': 'I',
		'Î«': 'Y',
		'α': 'a', 'β': 'b', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'h', 'θ': '8',
		'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': '3', 'ο': 'o', 'π': 'p',
		'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'y', 'φ': 'f', 'χ': 'x', 'ψ': 'ps', 'ω': 'w',
		'Î¬': 'a', 'Î­': 'e', 'Î¯': 'i', 'Ï': 'o', 'Ï': 'y', 'Î®': 'h', 'Ï': 'w', 'ς': 's',
		'Ï': 'i', 'Î°': 'y', 'Ï': 'y', 'Î': 'i',

		// Turkish
		'Å': 'S', 'Ä°': 'I', 'Ç': 'C', 'Ü': 'U', 'Ö': 'O', 'Ä': 'G',
		'Å': 's', 'Ä±': 'i', 'ç': 'c', 'ü': 'u', 'ö': 'o', 'Ä': 'g', 

		// Russian
		'Ð': 'A', 'Ð': 'B', 'Ð': 'V', 'Ð': 'G', 'Ð': 'D', 'Ð': 'E', 'Ð': 'Yo', 'Ð': 'Zh',
		'Ð': 'Z', 'Ð': 'I', 'Ð': 'J', 'Ð': 'K', 'Ð': 'L', 'Ð': 'M', 'Ð': 'N', 'Ð': 'O',
		'Ð': 'P', 'Ð ': 'R', 'Ð¡': 'S', 'Ð¢': 'T', 'Ð£': 'U', 'Ð¤': 'F', 'Ð¥': 'H', 'Ð¦': 'C',
		'Ð§': 'Ch', 'Ð¨': 'Sh', 'Ð©': 'Sh', 'Ðª': '', 'Ð«': 'Y', 'Ð¬': '', 'Ð­': 'E', 'Ð®': 'Yu',
		'Ð¯': 'Ya',
		'Ð°': 'a', 'Ð±': 'b', 'Ð²': 'v', 'Ð³': 'g', 'Ð´': 'd', 'Ðµ': 'e', 'Ñ': 'yo', 'Ð¶': 'zh',
		'Ð·': 'z', 'Ð¸': 'i', 'Ð¹': 'j', 'Ðº': 'k', 'Ð»': 'l', 'Ð¼': 'm', 'Ð½': 'n', 'Ð¾': 'o',
		'Ð¿': 'p', 'Ñ': 'r', 'Ñ': 's', 'Ñ': 't', 'Ñ': 'u', 'Ñ': 'f', 'Ñ': 'h', 'Ñ': 'c',
		'Ñ': 'ch', 'Ñ': 'sh', 'Ñ': 'sh', 'Ñ': '', 'Ñ': 'y', 'Ñ': '', 'Ñ': 'e', 'Ñ': 'yu',
		'Ñ': 'ya',

		// Ukrainian
		'Ð': 'Ye', 'Ð': 'I', 'Ð': 'Yi', 'Ò': 'G',
		'Ñ': 'ye', 'Ñ': 'i', 'Ñ': 'yi', 'Ò': 'g',

		// Czech
		'Ä': 'C', 'Ä': 'D', 'Ä': 'E', 'Å': 'N', 'Å': 'R', 'Š': 'S', 'Å¤': 'T', 'Å®': 'U', 
		'Å½': 'Z', 
		'Ä': 'c', 'Ä': 'd', 'Ä': 'e', 'Å': 'n', 'Å': 'r', 'š': 's', 'Å¥': 't', 'Å¯': 'u',
		'Å¾': 'z', 

		// Polish
		'Ä': 'A', 'Ä': 'C', 'Ä': 'e', 'Å': 'L', 'Å': 'N', 'Ó': 'o', 'Å': 'S', 'Å¹': 'Z', 
		'Å»': 'Z', 
		'Ä': 'a', 'Ä': 'c', 'Ä': 'e', 'Å': 'l', 'Å': 'n', 'ó': 'o', 'Å': 's', 'Åº': 'z',
		'Å¼': 'z',

		// Latvian
		'Ä': 'A', 'Ä': 'C', 'Ä': 'E', 'Ä¢': 'G', 'Äª': 'i', 'Ä¶': 'k', 'Ä»': 'L', 'Å': 'N', 
		'Š': 'S', 'Åª': 'u', 'Å½': 'Z', 
		'Ä': 'a', 'Ä': 'c', 'Ä': 'e', 'Ä£': 'g', 'Ä«': 'i', 'Ä·': 'k', 'Ä¼': 'l', 'Å': 'n',
		'š': 's', 'Å«': 'u', 'Å¾': 'z'
	};
	
	// Transliterate characters to ASCII
    for(var k in char_map) {
        s = s.replace(RegExp(k, 'g'), char_map[k]);
    }
	
	// Replace non-alphanumeric characters with our delimiter
	var alnum = RegExp('[^a-z0-9]+', 'ig');
	s = s.replace(alnum, delimiter);
	
	// Remove duplicate delimiters
	s = s.replace(RegExp('[' + delimiter + ']{2,}', 'g'), delimiter);
		
	// Remove delimiter from ends
	s = s.replace(RegExp('(^' + delimiter + '|' + delimiter + '$)', 'g'), '');
	
	return s.toLowerCase();
}

export function createDownloadLink(content, filename, base64 = false, contentType = 'text/plain') {
    var link = document.createElement("a");
    let url;
    if(base64) {
        url = `data:${contentType};base64,${content}`;
    } else {
        url = window.URL.createObjectURL(new Blob([content]));
    }
    link.setAttribute("href", url);
    link.setAttribute("type", contentType);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
}

export function createAnchorFromUrl(url) {
    const urlRegex =/(\b(https?):\/\/[-A-Z0-9+#&=?@%_.]*[-A-Z0-9+#&=?@%_\/])/ig;
    return url.replace(urlRegex, match => `<a href="${match}" target="_blank">${match}</a>`);
}

export function hasPreference(prefKey, prop) {
    const ps = store.getters.preferenceByKey(prefKey);
    if(ps) {
        return ps[prop] || ps;
    }
}

export function getPreference(prefKey) {
    return store.getters.preferenceByKey(prefKey);
}

export function getProjectName(slug = false) {
    const name = getPreference('prefs.project-name');
    return slug ? slugify(name) : name;
}

export function setPreference(prefKey, value) {
    this.state.preferences[prefKey] = value;
}

export function getTabPlugins() {
    return this.state.plugins.tab;
}

export function getToolPlugins() {
    return this.state.plugins.tools;
}

export function getSettingsPlugins() {
    return this.state.plugins.settings;
}

export function getPlugins() {
    return this.state.plugins;
}

export function addEntityType(entityType) {
    this.state.entityTypes[entityType.id] = entityType;
}

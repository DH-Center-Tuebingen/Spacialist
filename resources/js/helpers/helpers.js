// import auth from '@/bootstrap/auth.js';
import router from '%router';
import useEntityStore from '@/bootstrap/stores/entity.js';
import useSystemStore from '@/bootstrap/stores/system.js';
import useUserStore from '@/bootstrap/stores/user.js';

import {
    showError
} from '@/helpers/modal.js';

import {
    splitColor,
} from '@/helpers/colors.js';

export function can(permissionString, oneOf) {
    oneOf = oneOf || false;
    const user = useUserStore().user;
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

export function hasPlugin(id) {
    return useSystemStore().hasPlugin(id);
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


const UNSET_CERTAINTY = {type: 'unset', icon: 'far fa-fw fa-circle-check', rangeFunction: (certainty) => certainty == null || certainty < 0 || certainty > 100};
export function getCertainties() {
    function inRangeOf(lowIn, highEx) {
        return (certainty) => parseFloat(certainty) >= lowIn && parseFloat(certainty) < highEx;
    }
    return [
        {type: 'danger', icon: 'fas fa-fw fa-circle-exclamation', rangeFunction: inRangeOf(0, 25)},
        {type: 'warning', icon: 'fas fa-fw fa-circle-exclamation', rangeFunction: inRangeOf(25, 100)},
        {type: 'success', icon: 'fas fa-fw fa-circle-check', rangeFunction: (certainty) => certainty === 100},
        UNSET_CERTAINTY,
    ];
}

export function getCertainty(value) {
    for(const certainty of getCertainties()) {
        if(certainty.rangeFunction(value)) {
            return certainty;
        }
    }

    return UNSET_CERTAINTY;
}

export function getCertaintyClass(certainty, prefix = 'bg') {
    const classes = [];
    const cert = getCertainty(certainty);
    classes.push(`${prefix}-${cert.type}`);
    return classes;
}

export const multiselectResetClasslist = {clear: 'multiselect-clear multiselect-clear-reset'};

export function getInputCursorPosition(input) {
    const div = document.createElement('div');
    const compStyle = getComputedStyle(input);
    for(const k of compStyle) {
        div.style[k] = compStyle[k];
    }
    div.style.height = 'auto';
    div.textContent = input.value.substr(0, input.selectionStart);

    const span = document.createElement('span');
    span.textContent = input.value.substr(input.selectionStart) || '.';
    div.appendChild(span);
    document.body.appendChild(div);
    const offsetX = span.offsetLeft + input.offsetLeft;
    const offsetY = span.offsetTop + input.offsetTop;
    document.body.removeChild(div);

    return {
        x: offsetX,
        y: offsetY,
    };
}

export function getTs() {
    const d = new Date();
    return d.getTime();
}

export function getOrderedDate(short = false, withTime = false) {
    const d = new Date();
    const iso = d.toISOString();
    if(withTime) {
        return iso;
    } else {
        const woTime = iso.substring(0, iso.indexOf('T'));
        if(short) {
            return woTime.replaceAll('-', '');
        } else {
            return woTime;
        }
    }
}

export function randomId(min = 0, max = 100) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min) + min);
}

export function getConcept(url) {
    if(!url) {
        return {};
    }
    return useSystemStore().concepts[url] || {};
}

export function hasConcept(url) {
    if(!url) return false;
    return !!useSystemStore().concepts[url];
}

export function translateLabel(element, prop) {
    const value = element[prop];
    if(!value) return element;
    return translateConcept(value);
}

export function translateConcept(url) {
    return useSystemStore().translateConcept(url);
}

export function getConceptLabel(concept) {
    return concept.labels.length ? concept.labels[0].label : '';
}

export function getConceptNote(url) {
    return getConcept(url)?.note;
}

export function translateEntityType(id) {
    return translateConcept(useEntityStore().getEntityType(id).thesaurus_url);
}

export function getEntityTypeAttributes(id, exclude = false) {
    if(!id) return [];
    return useEntityStore().getEntityTypeAttributes(id, exclude) || [];
}

export function getEntityTypeAttribute(etid, aid, exclude = false) {
    if(!etid || !aid) return null;
    const attributes = getEntityTypeAttributes(etid, exclude);
    return attributes ? attributes.find(a => a.id == aid) : null;
}

// TODO: the return value for a given aid differs from
// what is returned for the whole entity type
// Currently this method is called two times:
// One with aid, one without and it's expected to have different behaviour
// It might be better to split this method into two separate methods [VR]
export function getEntityTypeDependencies(id, aid) {
    if(!id) return {};
    const attributes = getEntityTypeAttributes(id);
    if(!attributes) return {};
    if(!!aid) {
        const attr = attributes.find(a => a.id == aid);
        return attr?.pivot?.depends_on || {};
    } else {
        const dependencies = {};
        attributes.forEach(attribute => {
            dependencies[attribute.id] = attribute?.pivot?.depends_on || {};
        });
        return dependencies;
    }
}

export function getEntityTypeDependencyTriggers(id) {
    if(!id) return {};
    const attributes = getEntityTypeAttributes(id);

    const dependencyTriggers = {};
    attributes.forEach(a => {
        if(!!a.pivot.depends_on) {
            const deps = a.pivot.depends_on;
            deps.groups.forEach(group => {
                group.rules.forEach(rule => {
                    if(!dependencyTriggers[rule.on]) {
                        dependencyTriggers[rule.on] = [];
                    }
                    dependencyTriggers[rule.on].push(a.id);
                });
            });
        }
    });
    return dependencyTriggers;
}

export function isAllowedSubEntityType(parentId, id) {
    const parent = useEntityStore().getEntityType(parentId);
    if(!parent) return false;
    return parent.sub_entity_types.some(et => et.id == id);
}

export function getInitialAttributeValue(attribute, typeAttr = 'type') {
    switch(attribute[typeAttr]) {
        case 'string':
        case 'stringf':
        case 'richtext':
        case 'iconclass':
        case 'rism':
        case 'geography':
        case 'date':
        case 'url':
            return '';
        case 'integer':
        case 'double':
        case 'boolean':
            return 0;
        case 'percentage':
            return 50;
        case 'serial':
            let str = attribute.textContent;
            let toRepl = '%d';
            let ctr = '1954';
            if(!str) {
                str = 'Find_%05d_Placeholder';
            }
            let hasIdentifier = false;
            let isSimple = true;
            let matches = str.match(/.*(%d).*/);
            if(matches && matches[1]) {
                hasIdentifier = true;
                isSimple = true;
            } else {
                matches = str.match(/.*(%\d*d).*/);
                if(matches && matches[1]) {
                    hasIdentifier = true;
                    isSimple = false;
                }
            }
            if(hasIdentifier && !isSimple) {
                toRepl = matches[1];
                let pad = parseInt(toRepl.substring(1, toRepl.length - 1));
                ctr = ctr.padStart(pad, '0');
            }
            return str.replaceAll(toRepl, ctr);
        case 'list':
        case 'string-mc':
        case 'entity-mc':
        case 'userlist':
        case 'daterange':
        case 'table':
            return [];
        case 'sql':
            return t('global.preview_not_available');
        case 'epoch':
        case 'dimension':
        case 'entity':
        case 'string-sc':
            return {};
        case 'si-unit':
            if(!attribute.siGroup) {
                return { value: 0 };
            } else {
                return {
                    value: 0,
                    unit: attribute.siGroup,
                    default: attribute.siGroupUnit,
                };
            }
        default:
            return '';
    }
}

export function getEmptyAttributeValue(type) {
    if(!type) return null;

    switch(type) {
        case 'boolean':
            return false;
        case 'dimension':
        case 'entity':
        case 'epoch':
        case 'timeperiod':
            return {};
        case 'float':
        case 'integer':
        case 'percentage':
            return;
        case 'daterange':
        case 'entity-mc':
        case 'list':
        case 'string-mc':
        case 'table':
        case 'userlist':
            return [];
        case 'serial':
        case 'sql':
            return null;
        case 'date':
        case 'geography':
        case 'iconclass':
        case 'richtext':
        case 'rism':
        case 'string-sc':
        case 'string':
        case 'stringf':
        case 'system-separator':
        case 'url':
        default:
            return '';
    }
}

export function getAttributeValueAsString(rawValue, datatype) {
    if(!rawValue || !datatype) {
        return null;
    }

    let strValue = null;

    switch(datatype) {
        case 'string':
        case 'stringf':
        case 'richtext':
        case 'double':
        case 'integer':
        case 'boolean':
        case 'date':
        case 'geography':
        case 'percentage':
        case 'serial':
        case 'iconclass':
        case 'rism':
            strValue = rawValue;
            break;
        case 'epoch':
        case 'timeperiod':
            strValue = `${rawValue.start} (${rawValue.startLabel}) - ${rawValue.end} (${rawValue.endLabel})`;
            if(datatype == 'epoch') {
                strValue += `[${translateConcept(rawValue.epoch.concept_url)}]`;
            }
            break;
        case 'dimension':
            strValue = `${rawValue.B} x ${rawValue.H} x ${rawValue.T} ${rawValue.unit}`;
            break;
        case 'list':
            strValue = rawValue.join(', ');
            break;
        case 'string-sc':
        case 'string-mc':
        case 'entity':
        case 'entity-mc':
        case 'userlist':
        case 'table':
        case 'sql':
            strValue = `TODO: ${datatype}`;
            break;
    }

    return strValue;
}

// Fills non-present attribute values to be used in draggable components (e.g. attribute-list)
export function fillEntityData(data, etid) {
    const attrs = getEntityTypeAttributes(etid);
    for(let i = 0; i < attrs.length; i++) {
        const currAttr = attrs[i];
        if(!data[currAttr.id]) {
            data[currAttr.id] = {
                value: getInitialAttributeValue(currAttr, 'datatype'),
            };
        }
    }
    return data;
}

// Formula based on https://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color/3943023#3943023
export function calculateEntityTypeColors(entityType, alpha = 0.5) {
    if(!entityType || !entityType.layer) return {};
    let r, g, b, a;
    [r, g, b] = splitColor(entityType.layer.color);
}
export function calculateEntityColors(id, alpha = 0.5) {
    const et = useEntityStore().getEntityType(id);
    let typeColor = null;
    if(et?.layer?.color) {
        typeColor = et.layer.color;
    } else if(et.color) {
        typeColor = et.color;
    } else {
        return {};
    }
    let r, g, b, a;
    [r, g, b] = splitColor(typeColor);
    const cs = [r, g, b].map(c => {
        c /= 255.0;
        if(c <= 0.03928) c /= 12.92;
        else c = Math.pow(((c + 0.055) / 1.055), 2.4);
        return c;
    });
    // let cont = r*0.299 + g*0.587 + b*0.114;
    const l = cs[0] * 0.2126 + cs[1] * 0.7152 + cs[2] * 0.0722;

    // const textColor = cont > 150 ? '#000000' : '#ffffff';
    const textColor = l > 0.179 ? '#000000' : '#ffffff';
    const color = `rgba(${r}, ${g}, ${b}, ${alpha})`;
    return {
        color: textColor,
        backgroundColor: color
    };
}

export function siSymbolToStr(symbol) {
    if(!symbol) return '';

    if(Array.isArray(symbol)) {
        return symbol[0];
    } else {
        return symbol;
    }
}

export function isLoggedIn() {
    return useUserStore().userLoggedIn;
}

export function getUser() {
    return isLoggedIn() ? useUserStore().getCurrentUser : {};
}

export function getUsers() {
    return useUserStore().users;
}

export function userId() {
    return getUser().id || -1;
}

// where can be any of 'start', 'end', 'whole' (default)
export function filterUsers(term, ci = true, where = 'whole') {
    const flags = ci ? 'i' : '';
    let pattern = term;
    if(where == 'start') {
        pattern = `^${pattern}`;
    } else if(where == 'end') {
        pattern = `${pattern}$`;
    }
    const regex = new RegExp(pattern, flags);
    return getUsers().filter(u => {
        return regex.test(u.name) || regex.test(u.nickname);
    });
}

export function throwError(error) {
    console.error(error);
    if(error.response) {
        const r = error.response;
        const req = {
            status: r.status,
            url: r.config.url,
            method: r.config.method.toUpperCase()
        };
        showErrorModal(r.data, r.headers, req);
    } else if(error.request) {
        showErrorModal(error.request);
    } else {
        showErrorModal(error.message || error);
    }
}

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
}

export function findInList(list, searchValue, searchKey = 'id', recKey = 'children') {
    if(!list || list.length == 0) return;

    for(let i = 0; i < list.length; i++) {
        if(list[i][searchKey] == searchValue) {
            return list[i];
        }
        const gotIt = findInList(list[i][recKey], searchValue, searchKey, recKey);
        if(gotIt) return gotIt;
    }
}

export function only(object, allows = []) {
    return Object.keys(object)
        .filter(key => allows.includes(key))
        .reduce((obj, key) => {
            return {
                ...obj,
                [key]: object[key]
            };
        }, {});
}

export function except(object, excepts = []) {
    return Object.keys(object)
        .filter(key => !excepts.includes(key))
        .reduce((obj, key) => {
            return {
                ...obj,
                [key]: object[key]
            };
        }, {});
}

export function getElementAttribute(el, attribute, defaultValue, type = 'string') {
    let value = el.getAttribute(attribute);
    if(value) {
        if(type == 'int') {
            value = parseInt(value);
        } else if(type == 'bool') {
            value = value === true || value == 'true' || value == 1;
        }
    }
    return value || defaultValue;
}

export function isArray(arr) {
    return Array.isArray(arr);
}

export { default as _cloneDeep } from 'lodash/cloneDeep';
export { default as _debounce } from 'lodash/debounce';
export { default as _throttle } from 'lodash/throttle';
export { default as _orderBy } from 'lodash/orderBy';

export function showErrorModal(errorMsg, headers, request) {
    showError({
        msg: errorMsg,
        headers: headers,
        request: request,
    });
}

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

export function getClassByValidation(errorList) {
    return {
        // 'is-valid': !msgObject[field],
        'is-invalid': !!errorList && errorList.length > 0,
    };
}

export function createAnchorFromUrl(url) {
    if(!url) return url;
    if(typeof url != 'string' || !url.replace) return url;
    const urlRegex = /(\b(https?):\/\/[-A-Z0-9+#&=?@%_.]*[-A-Z0-9+#&=?@%_\/])/ig;
    return url.replace(urlRegex, match => `<a href="${match}" target="_blank">${match}</a>`);
}

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

export function hash(str) {
    let hash = 0;
    for(let i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash |= 0;
    }
    return hash;
}

// UNVERIFIED

export function getNotificationSourceLink(notification) {
    const currentRoute = router.currentRoute.value;
    const query = currentRoute.query;
    if(notification.type == 'App\\Notifications\\CommentPosted') {
        switch(simpleResourceType(notification.data.resource?.type)) {
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
                };
            case 'attribute_value':
                return {
                    name: 'entityrefs',
                    params: {
                        id: notification.data.resource.meta.entity_id,
                        aid: notification.data.resource.meta.attribute_id,
                    },
                    query: query
                };
            default:
                return null;
        }
    } else if(notification.type == 'App\\Notifications\\EntityUpdated') {
        return {
            name: 'entitydetail',
            params: {
                id: notification.data.resource.id
            },
            query: {
                ...query,
                view: 'attributes',
            }
        };
    }
}

export async function asyncFor(arr, callback) {
    for(let i = 0; i < arr.length; i++) {
        await callback(arr[i]);
    }
}

export function createDownloadLink(content, filename, base64 = false, contentType = 'text/plain') {
    var link = document.createElement('a');
    let url;
    if(base64) {
        url = `data:${contentType};base64,${content}`;
    } else {
        url = window.URL.createObjectURL(new Blob([content]));
    }
    link.setAttribute('href', url);
    link.setAttribute('type', contentType);
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
}

export function copyToClipboard(elemId) {
    const range = document.createRange();
    const selection = window.getSelection();
    const elem = document.getElementById(elemId);
    range.selectNodeContents(elem);
    selection.removeAllRanges();
    selection.addRange(range);
    try {
        document.execCommand('copy');
        selection.removeAllRanges();
    } catch(err) {
        console.log(err);
    }
}

export function sortConcepts(ca, cb) {
    const systemStore = useSystemStore();
    return systemStore.translateConcept(ca.concept_url).localeCompare(systemStore.translateConcept(cb.concept_url));
}

export function sortTranslated(asc = true, prop = 'thesaurus_url') {
    const systemStore = useSystemStore();
    return function(a, b) {
        return systemStore.translateConcept(a[prop]).localeCompare(systemStore.translateConcept(b[prop])) * (asc ? 1 : -1);
    };
}

export function sortAlphabetically(asc = true) {
    return function(a, b) {
        return a.localeCompare(b) * (asc ? 1 : -1);
    };
}

export function sortAlphabeticallyBy(prop = 'name', asc = true) {
    return function(a, b) {
        return sortAlphabetically(asc)(a[prop], b[prop]);
    };
}

export function sortByLength(secondarySort = null, asc = true) {
    return function(a, b) {
        if(secondarySort && a.length == b.length) {
            for(let i = 0; i < a.length; i++) {
                const order = secondarySort(a[i], b[i]);
                if(order != 0) {
                    return order;
                }
            }
            return 0;
        }
        return a.length - b.length;
    };
}
import dayjs from '@/bootstrap/time.js';

import useUserStore from '@/bootstrap/stores/user.js';

export function date(value, format = 'DD.MM.YYYY HH:mm') {
    if(value) {
        let d;
        if(isNaN(value)) {
            d = dayjs.utc(value);
        } else {
            d = dayjs.utc(value*1000);
        }
        return d.format(format);
    }
}
export function datestring(value) {
    if(value) {
        const d = isNaN(value) ? dayjs.utc(value) : dayjs.utc(value*1000);
        return d.toDate().toString();
    }
}
export function ago(value) {
    if(value) {
        let d;
        if(isNaN(value)) {
            d = dayjs.utc(value);
        } else {
            d = dayjs.utc(value*1000);
        }
        return d.fromNow();
    }
}
export function numPlus(value, length = 2) {
    if(value) {
        const v = Math.floor(value);
        const max = Math.pow(10, length) - 1;
        if(v > max) return `${max.toString(10)}+`;
        else return v;
    } else {
        return value;
    }
}
export function time(value, withHours) {
    if(value) {
        let hours = 0;
        let rHours = 0;
        if(withHours) {
            hours = parseInt(Math.floor(value / 3600));
            rHours = hours * 3600;
        }
        const minutes = parseInt(Math.floor((value-rHours) / 60));
        const rMin = minutes * 60;
        const seconds = parseInt(Math.floor(value - rHours - rMin));

        const paddedH = hours > 9 ? hours : `0${hours}`;
        const paddedM = minutes > 9 ? minutes : `0${minutes}`;
        const paddedS = seconds > 9 ? seconds : `0${seconds}`;

        if(withHours) {
            return `${paddedH}:${paddedM}:${paddedS}`;
        } else {
            return `${paddedM}:${paddedS}`;
        }
    } else {
        if(withHours) {
            return '00:00:00';
        } else {
            return '00:00';
        }
    }
}
export function length(value, precision = 2, isArea = false) {
    if(!value) return value;

    const length = parseFloat(value);

    if(!isFinite(value) || isNaN(length)) {
        return length;
    }
    let unit;
    let factor;
    if(isArea) {
        if(length < 0.00001) {
            unit = 'mm²';
            factor = 100000;
        } else if(length < 0.01) {
            unit = 'cm²';
            factor = 10000;
        } else if(length < 100) {
            unit = 'm²';
            factor = 1;
        } else if(length < 100000) {
            unit = 'ha';
            factor = 0.0001;
        } else {
            unit = 'km²';
            factor = 0.000001;
        }
    } else {
        if(length < 0.01) {
            unit = 'mm';
            factor = 1000;
        } else if(length < 1) {
            unit = 'cm';
            factor = 100;
        } else if(length < 1000) {
            unit = 'm';
            factor = 1;
        } else {
            unit = 'km';
            factor = 0.001;
        }
    }

    const sizeInUnit = length * factor;
    return sizeInUnit.toFixed(precision) +  ' ' + unit;
}
export function bytes(value, precision = 2, hideZeros = false) {
    if(!value) return value;

    const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const bytes = parseFloat(value);

    let unitIndex;
    if(!isFinite(value) || isNaN(bytes)) {
        unitIndex = 0;
    } else {
        unitIndex = Math.floor(Math.log(bytes) / Math.log(1024));
    }

    const unit = units[unitIndex];
    const sizeInUnit = bytes / Math.pow(1024, unitIndex);
    let finalValue = sizeInUnit;
    if(!hideZeros || !Number.isInteger(sizeInUnit)) {
        finalValue = finalValue.toFixed(precision);
    }
    return `${finalValue} ${unit}`;
}
export function toFixed(value, precision = 2) {
    if(precision < 0) precision = 2;
    return value ? value.toFixed(precision) : value;
}
export function truncate(str, length = 80, ellipses = '…') {
    if(length < 0) length = 80;
    if(str) {
        if(str.length <= length) {
            return str;
        }
        return str.slice(0, length) + ellipses;
    }
    return str;
}
export function highlight(str, hl) {
    if(!str) return str;
    if(!hl) return str;
    const regex = new RegExp(`(${hl})`, 'gi');
    return str.replaceAll(regex, `<mark class="p-0">$1</mark>`);
}
export function ucfirst(str) {
    if(!str) return str;
    return str[0].toUpperCase() + str.substring(1);
}
export function kebab2pascalcase(str) {
    if(!str) return str;

    return str.split('-').map(word => ucfirst(word)).join('');
}
export function escapehtml(str) {
    return str
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;');
}
export function join(arr, separator = ', ') {
    if(!arr && !Array.isArray(arr)) return arr;

    return arr.join(separator);
}
export function bibtexEntryToText(value, key = null) {
    let text = value;
    if(value.startsWith('{{') && value.endsWith('}}')) {
        text = value.substring(2, value.length-2);
    } else if(value.startsWith('{') && value.endsWith('}')) {
        text = value.substring(1, value.length-1);
    } else if(key && key == 'pages') {
        text = value.replace('--', ' - ');
    }
    return text;
}
export function bibtexify(value, type) {
    let rendered = '<pre class=\'mb-0\'><code class=\'fs-1r\'>';
    if(type) {
        rendered += '@'+type+' {';
        if(value['citekey']) rendered += value['citekey'] + ',';
        for(let k in value) {
            if(value[k] == null || value[k] == '' || k == 'citekey') continue;
            let cv = escapehtml(value[k]);
            if(!((cv.startsWith('{{') && cv.endsWith('}}')) ||
                (cv.startsWith('{') && cv.endsWith('}')))) {
                cv = '"' + cv + '"';
            }
            rendered += '    <br />';
            rendered += '    ' + k + ' = ' + cv;
        }
        rendered += '<br />';
        rendered += '}';
    }
    rendered += '</code></pre>';
    return rendered;
}
export function mentionify(value) {
    const userStore = useUserStore();
    const template = `<span class="badge bg-primary">@{name}</span>`;
    const unknownTemplate = `<span class="fw-bold">@{name}</span>`;
    const mentionRegex = /@(\w|\d)+/gi;
    let mentions = value.match(mentionRegex);
    if(!mentions) return value;
    mentions = mentions.filter((m, i) => mentions.indexOf(m) === i);
    let newValue = value;
    for(let i=0; i<mentions.length; i++) {
        const elem = mentions[i];
        const m = elem.substring(1);
        const user = userStore.getUserBy(m, 'nickname');
        const replRegex = new RegExp(elem, 'g');
        let name = m;
        let tpl = unknownTemplate;
        if(user) {
            name = user.name;
            tpl = template;
        }
        newValue = newValue.replace(replRegex, tpl.replace('{name}', name));
    }
    return newValue;
}
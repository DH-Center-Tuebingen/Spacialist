const EQUAL = '=';
const NOT_EQUAL = '!=';
const LESS_THAN = '<';
const LESS_THAN_OR_EQUAL = '<=';
const GREATER_THAN = '>';
const GREATER_THAN_OR_EQUAL = '>=';
const IS_SET = '?';
const IS_NOT_SET = '!?';

const operators = [
    EQUAL,
    NOT_EQUAL,
    LESS_THAN,
    LESS_THAN_OR_EQUAL,
    GREATER_THAN,
    GREATER_THAN_OR_EQUAL,
    IS_SET,
    IS_NOT_SET,
];

export function evaluateRule(type, value, rule) {
    let tmpMatch = false;
    switch(rule.operator) {
        case '=':
            if(type == 'string-sc') {
                tmpMatch = value?.id == rule.value;
            } else if(type == 'string-mc') {
                tmpMatch = value && value.some(mc => mc.id == rule.value);
            } else {
                tmpMatch = value == rule.value;
            }
            break;
        case '!=':
            if(type == 'string-sc') {
                tmpMatch = value?.id != rule.value;
            } else if(type == 'string-mc') {
                tmpMatch = Array.isArray(value) && value.every(mc => mc.id != rule.value);
            } else {
                tmpMatch = value != rule.value;
            }
            break;
        case '<':
            tmpMatch = value < rule.value;
            break;
        case '>':
            tmpMatch = value > rule.value;
            break;
        case '<=':
            tmpMatch = value <= rule.value;
            break;
        case '>=':
            tmpMatch = value >= rule.value;
            break;
        case '?':
        case '!?':
            if(type == 'string-sc') {
                tmpMatch = value?.id !== undefined;
            } else if(type == 'string-mc') {
                tmpMatch = Array.isArray(value) && value.length > 0;
            } else {
                tmpMatch = value != null && value != '' && value != 0;
            }
            if(rule.operator == '!?') {
                tmpMatch = !tmpMatch;
            }
            break;
        default:
            console.error('Unknown operator: ' + rule.operator);
            break;
    }
    return tmpMatch;
}

export function getEmptyGroup(union = false) {
    return {
        union,
        rules: [],
    };
}

export function getOperatorsForDatatype(datatype) {
    const list = [EQUAL, NOT_EQUAL];
    switch(datatype) {
        case 'epoch':
        case 'timeperiod':
        case 'dimension':
        case 'list':
        case 'table':
        case 'sql':
            break;
        // TODO handle entity attributes
        case 'entity':
        case 'entity-mc':
        case 'userlist':
            break;
        case 'string':
        case 'stringf':
        case 'richtext':
        case 'string-sc':
        case 'string-mc':
        case 'geography':
        case 'iconclass':
        case 'rism':
        case 'serial':
            operators.forEach(o => {
                switch(o.id) {
                    case 1:
                    case 2:
                        list.push(o);
                        break;
                }
            });
            break;
        case 'double':
        case 'integer':
        case 'date':
        case 'percentage':
            operators.forEach(o => {
                switch(o.id) {
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                        list.push(o);
                        break;
                }
            });
            break;
        case 'boolean':
            break;
        default:
            throw new Error(`Unsupported datatype ${datatype}`);
    }
    return list;
}


export const getInputTypeClass = datatype => {
    switch(datatype) {
            case 'string':
            case 'stringf':
            case 'richtext':
            case 'geography':
            case 'iconclass':
            case 'rism':
            case 'serial':
                return 'text';
            case 'double':
            case 'integer':
            case 'percentage':
                return 'number';
            case 'boolean':
                return 'boolean';
            case 'date':
                return 'date';
            case 'string-sc':
            case 'string-mc':
                return 'select';
            // TODO handle entity attributes
            case 'entity':
            case 'entity-mc':
                // return 'entity';
            case 'userlist':
            case 'epoch':
            case 'timeperiod':
            case 'dimension':
            case 'list':
            case 'table':
            case 'sql':
            default:
                return 'unsupported';
        }
};
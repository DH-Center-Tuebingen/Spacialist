const EQUAL = { name: 'equal', operator: '=' };
const NOT_EQUAL = { name: 'not_equal', operator: '!=' };
const LESS_THAN = { name: 'less_than', operator: '<' };
const LESS_THAN_OR_EQUAL = { name: 'less_or_equal', operator: '<=' };
const GREATER_THAN = { name: 'greater', operator: '>' };
const GREATER_THAN_OR_EQUAL = { name: 'greater_or_equal', operator: '>=' };
const IS_SET = { name: 'is_set', operator: '?', no_parameter: true };
const IS_NOT_SET = { name: 'is_not_set', operator: '!?', no_parameter: true };

const operators = [
    EQUAL,
    NOT_EQUAL,
    LESS_THAN,
    LESS_THAN_OR_EQUAL,
    GREATER_THAN,
    GREATER_THAN_OR_EQUAL,
    IS_SET,
    IS_NOT_SET
];

for(let i = 0; i < operators.length; i++) {
    const operator = operators[i];
    operator.id = i + 1;
}

export function evaluateRule(type, value, rule) {
    let tmpMatch = false;
    switch(rule.operator) {
        case EQUAL.operator:
            if(type == 'string-sc') {
                tmpMatch = value?.id == rule.value;
            } else if(type == 'string-mc') {
                tmpMatch = value && value.some(mc => mc.id == rule.value);
            } else {
                tmpMatch = value == rule.value;
            }
            break;
        case NOT_EQUAL.operator:
            if(type == 'string-sc') {
                tmpMatch = value?.id != rule.value;
            } else if(type == 'string-mc') {
                tmpMatch = Array.isArray(value) && value.every(mc => mc.id != rule.value);
            } else {
                tmpMatch = value != rule.value;
            }
            break;
        case LESS_THAN.operator:
            tmpMatch = value < rule.value;
            break;
        case GREATER_THAN.operator:
            tmpMatch = value > rule.value;
            break;
        case LESS_THAN_OR_EQUAL.operator:
            tmpMatch = value <= rule.value;
            break;
        case GREATER_THAN_OR_EQUAL.operator:
            tmpMatch = value >= rule.value;
            break;
        case IS_SET.operator:
        case IS_NOT_SET.operator:
            if(type == 'string-sc') {
                tmpMatch = value?.id !== undefined;
            } else if(type == 'string-mc') {
                tmpMatch = Array.isArray(value) && value.length > 0;
            } else {
                tmpMatch = value != null && value != '' && value != 0;
            }
            if(rule.operator == IS_NOT_SET.operator) {
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
        case 'boolean':
        case 'dimension':
        case 'entity-mc':
        case 'entity':
        case 'epoch':
        case 'list':
        case 'sql':
        case 'table':
        case 'timeperiod':
        case 'userlist':
            break;
        case 'geography':
        case 'iconclass':
        case 'richtext':
        case 'rism':
        case 'serial':
        case 'string-mc':
        case 'string-sc':
        case 'string':
        case 'stringf':
        case 'url':
            list.push(
                IS_SET,
                IS_NOT_SET
            );
            break;
        case 'date':
        case 'double':
        case 'integer':
        case 'percentage':
        case 'si-unit':
            list.push(
                LESS_THAN,
                LESS_THAN_OR_EQUAL,
                GREATER_THAN,
                GREATER_THAN_OR_EQUAL,
            );
            break;
        default:
            throw new Error(`Unsupported datatype ${datatype}`);
    }
    console.log(list);
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
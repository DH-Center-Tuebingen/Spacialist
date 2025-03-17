import useAttributeStore from '@/bootstrap/stores/attribute.js';

const EQUAL = { name: 'equal', operator: '=' };
const NOT_EQUAL = { name: 'not_equal', operator: '!=' };
const LESS = { name: 'less', operator: '<' };
const LESS_OR_EQUAL = { name: 'less_or_equal', operator: '<=' };
const GREATER = { name: 'greater', operator: '>' };
const GREATER_OR_EQUAL = { name: 'greater_or_equal', operator: '>=' };
const IS_SET = { name: 'set', operator: '?', no_parameter: true };
const IS_NOT_SET = { name: 'not_set', operator: '!?', no_parameter: true };

export const operators = [
    EQUAL,
    NOT_EQUAL,
    LESS,
    LESS_OR_EQUAL,
    GREATER,
    GREATER_OR_EQUAL,
    IS_SET,
    IS_NOT_SET
];

export const operatorMap = {};

for(let i = 0; i < operators.length; i++) {
    const operator = operators[i];
    operator.id = i + 1;
    operatorMap[operator.operator] = operator;
}

export const getOperatorBySymbol = symbol => {
    return operatorMap[symbol];
};

function checkIfSet(type, value) {
    let tmpMatch = false;
    if(type == 'string-sc') {
        tmpMatch = value?.id !== undefined;
    } else if(type == 'string-mc') {
        tmpMatch = Array.isArray(value) && value.length > 0;
    } else {
        tmpMatch = value != null && value != '' && value != 0;
    }
    return tmpMatch;
}

function checkIfEqual(type, value, ruleValue) {
    let match = true;
    if(type == 'string-sc' || type == 'entity') {
        match = value?.id == ruleValue;
    } else if(type == 'string-mc' || type == 'entity-mc') {
        for(const ruleValueId of ruleValue) {
            const itemMatch = value.find(v => v.id == ruleValueId);
            if(itemMatch === undefined) {
                match = false;
                break;
            }
        }
    } else if(type == 'list') {
        match = value.length === ruleValue.length && value.every((v, i) => v === ruleValue[i]);
    } else {
        match = value == ruleValue;
    }
    return match;
}

export function evaluateRule(type, value, rule) {
    let ruleValue = rule.value;

    if(type == 'date') {
        value = new Date(value).getTime();
        ruleValue = new Date(ruleValue).getTime();
    }

    if(type == 'si-unit') {
        value = value?.normalized;
    }

    switch(rule.operator) {
        case EQUAL.operator:
            return checkIfEqual(type, value, ruleValue);
        case NOT_EQUAL.operator:
            return !checkIfEqual(type, value, ruleValue);
        case LESS.operator:
            return value < ruleValue;
        case GREATER.operator:
            return value > ruleValue;
        case LESS_OR_EQUAL.operator:
            return value <= ruleValue;
        case GREATER_OR_EQUAL.operator:
            return value >= ruleValue;
        case IS_SET.operator:
            return checkIfSet(type, value);
        case IS_NOT_SET.operator:
            return !checkIfSet(type, value);
        default:
            console.error('Unknown operator: ' + rule.operator);
            // Ignore the rule if it is not valid.
            return true;
    }
}

export function getEmptyGroup(or = false) {
    return {
        or,
        rules: [],
    };
}

export function getOperatorsForDatatype(datatype) {
    const EQUALITY = [EQUAL, NOT_EQUAL];
    const SET = [IS_SET, IS_NOT_SET];
    const COMPARISON = [LESS, LESS_OR_EQUAL, GREATER, GREATER_OR_EQUAL];

    switch(datatype) {
        case 'boolean':
        case 'dimension':
        case 'entity-mc':
        case 'entity':
        case 'epoch':
        case 'table':
        case 'timeperiod':
        case 'userlist':
            return EQUALITY;
        case 'geography':
        case 'richtext':
            return SET;
        case 'iconclass':
        case 'list':
        case 'rism':
        case 'string-mc':
        case 'string-sc':
        case 'string':
        case 'stringf':
        case 'url':
            return [
                ...EQUALITY,
                ...SET,
            ];
        case 'date':
        case 'double':
        case 'integer':
        case 'percentage':
        case 'serial':
        case 'si-unit':
            return [
                ...EQUALITY,
                ...COMPARISON,
            ];
        default:
            throw new Error(`Unsupported datatype ${datatype}`);
    }
}

export const getInputTypeClass = datatype => {
    switch(datatype) {
        case 'si-unit':
        case 'url':
            return datatype;
        case 'string':
        case 'stringf':
        case 'richtext':
        case 'geography':
        case 'iconclass':
        case 'rism':
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
            return 'entity';
        case 'list':
            return 'list';
        case 'userlist':
        case 'serial':
        case 'epoch':
        case 'timeperiod':
        case 'dimension':
        case 'table':
        case 'sql':
        default:
            return 'unsupported';
    }
};

export const formatDependency = dependencyRules => {
    const formattedRules = {};
    formattedRules.or = !!dependencyRules?.or;
    if(dependencyRules.groups) {
        formattedRules.groups = dependencyRules.groups.map(group => {
            const formattedGroup = {};
            formattedGroup.or = group.or;
            formattedGroup.rules = group.rules.map(rule => {
                const converted = {
                    attribute: null,
                    operator: null,
                    value: null,
                };
                converted.attribute = useAttributeStore().getAttribute(rule.on);
                converted.operator = getOperatorBySymbol(rule.operator);
                converted.value = rule.value;
                return converted;
            });
            return formattedGroup;
        });
    } else if(Object.keys(dependencyRules).length == 0) {
        formattedRules.or = true;
        formattedRules.groups = [{
            or: false,
            rules: [],
        }];
    }

    return formattedRules;
};
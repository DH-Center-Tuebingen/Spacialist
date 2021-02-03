export function required(value) {
    return value && value.trim() ? true : 'global.validations.required';
};
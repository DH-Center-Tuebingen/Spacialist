export function isRequired(attribute) {
    return attribute?.pivot?.metadata?.required ?? false;
}

export function setRequired(attribute, required) {
    if(!attribute.pivot) {
        attribute.pivot = {};
    }
    if(!attribute.pivot.metadata) {
        attribute.pivot.metadata = {};
    }
    attribute.pivot.metadata.required = required;
}
export const kebabToPascal = kebabText => {
    const isKebabCase = /^[a-z]+(-[a-z]*)*$/.test(kebabText);
    if(!isKebabCase) return kebabText;
    return kebabText.replace(/(^[a-z]|-[a-z])/g, t => t.replace(/-/, '').toUpperCase());
};
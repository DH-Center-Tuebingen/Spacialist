/**
 * Ensures that a string starts with the specified prefix.
 * If the string already starts with the prefix, it is returned unchanged.
 * @param {string} str - String to check and potentially modify.
 * @param {string} prefix - Prefix that the string should start with.
 * @returns {string} - The original string if it already starts with the prefix, otherwise the string with the prefix prepended.
 */
export function ensureStartsWith(str, prefix) {
    if(!str.startsWith(prefix)) {
        return prefix + str;
    }
    return str;
}

/**
 * Ensures that a string does not start with the specified prefix.
 * If the string starts with the prefix, the prefix is removed.
 * @param {string} str - String to check and potentially modify.
 * @param {string} prefix - Prefix that the string should not start with.
 * @returns {string} - The original string if it does not start with the prefix, otherwise the string with the prefix removed.
 */
export function ensureNotToStartWith(str, prefix) {
    if(str.startsWith(prefix)) {
        return str.slice(prefix.length);
    }
    return str;
}
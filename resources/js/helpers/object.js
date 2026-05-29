
/**
 * Filters all child arrays of an object with a callback function.
 * Object is modified in place.
 * 
 * @param {object} object - Target object. 
 * @param {function} callback -  Filter function, called with (element, index, array) for each element in the array.
 */
export const filterAllChildArrays = (object, callback) => {
    for(const key in object) {
        filterChildArray(object, key, callback);
    }
}

export const filterChildArray = (object, key, callback) => {
    if(!object || !object[key]) return [];
    const array = object[key];
    if(!Array.isArray(array)) {
        console.warn('Child is not an array', key, object[key]);
        return object[key];
    }

    object[key] = array.filter(callback);
}
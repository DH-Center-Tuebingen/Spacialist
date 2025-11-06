

/**
 * Retrieve a value from an object using a dot-notation path.
 *
 * @param {Object} object - Target object to retrieve value from
 * @param {string} path - Dot-notation path to the desired value (e.g., "user.profile.name")
 * @param {*} [defaultValue=null] - Value to return if the path is not found
 * @returns {*} The value at the specified path, or defaultValue if not found
 * @example
 * const obj = { user: { profile: { name: 'John' } } };
 * getObjectValueByArray(obj, 'user.profile.name'); // returns 'John'
 * getObjectValueByArray(obj, 'user.age', 25); // returns 25
 */
export function getObjectValueByArray(object, path, defaultValue = null) {
  const pathArray = path.split(".")

  while(pathArray.length) {
    const key = pathArray.shift();
    // Check if the key exists in the object or return the default value
    if(object && Object.hasOwnProperty.call(object, key)) {
      object = object[key];
    } else {
      return defaultValue;
    }
  }
  return object;
}

/**
 * Set a value in an object using a dot-notation path.
 *
 * @param {Object} object - Target object to set value in
 * @param {string} path - Dot-notation path to the desired location (e.g., "user.profile.name")
 * @param {*} value - Value to set at the specified path
 * @returns {Object} The modified object
 * @example
 * const obj = { user: { profile: { name: 'John' } } };
 * setObjectValueByArray(obj, 'user.profile.name', 'Jane');
 * // obj.user.profile.name is now 'Jane'
 */
export function setObjectValueByArray(object, path, value, defaultValue = null) {
  const pathArray = path.split(".")

  while(pathArray.length) {
    const key = pathArray.shift();
    // Check if the key exists in the object or return the default value
    if(object && Object.hasOwnProperty.call(object, key)) {
      if(pathArray.length === 0) {
        object[key] = value;
      } else {
        object = object[key];
      }
    } else {
        console.log("setObjectValueByArray: path not found", defaultValue);
      return defaultValue;
    }
  }
  return object;
}
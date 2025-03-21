/**
 * Checks all values in the map.
 * If all are uniform, returns that value.
 * If not, returns mixedValue.
 *
 * @param {object} map - the map of the permissions
 * @param {array} rights - the rights as an array of strings to check
 * @param {number} mixedValue - returns this if the values are not uniform in the map - default is 3
 * @returns - the uniform value or the mixedValue
 */
export function determineUniformState(map, rights, mixedValue = 3) {
    let firstValue = mixedValue;
    for(let right of rights) {
        const currValue = map[right];
        if(firstValue === mixedValue) {
            firstValue = currValue;
        } else if(firstValue != currValue) {
            return mixedValue;
        }
    }
    return firstValue;
}
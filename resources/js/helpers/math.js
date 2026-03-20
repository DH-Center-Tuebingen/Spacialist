export function mod(a, b, allowNegative = false) {
    if(allowNegative) {
        return a % b;
    } else {
        // Normalize the result to be positive
        return ((a % b) + b) % b;
    }
}


/**
 * Takes an array of positive numbers and a target number, and adjusts the numbers in the array to integer values
 * so that their sum matches the target number, while staying close to their relative proportions.
 * 
 * @param {array} numbers 
 * @param {number} target 
 * @returns 
 */
export function scalePropotionallyInteger(array, target) {
    if(array.length === 0) {
        return array;
    }

    // We clone the array to avoid mutating the original one
    const output = array.slice();
    const total = array.reduce((acc, val) => acc + val, 0);

    // If only 0 values are set, set all to 1
    // to avoid division by 0.
    if(total === 0) {
        output.map(() => 1);
    }

    let last = null;
    let remainder = target;
    for(let [index, number] of output.entries()) {
        output[index] = Math.round(number / total * target);
        remainder -= output[index];
        last = index;
    }

    if(last) {
        output[output.length-1] += remainder;
    }

    return output;
}
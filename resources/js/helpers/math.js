export function mod(a, b, allowNegative = false) {
    if(allowNegative) {
        return a % b;
    } else {
        // Normalize the result to be positive
        return ((a % b) + b) % b;
    }
}
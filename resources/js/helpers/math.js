export function mod(a, b, allowNegative = false) {
    if(allowNegative) {
        return a % b;
    } else {
        return ((a % b) + b) % b;
    }
}
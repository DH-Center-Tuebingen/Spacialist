
/**
 *  Hook to add hotkeys to a number-like input field.
 * 
 * @param {Proxy} proxyValue - The ref value to be updated 
 * @param {Boolean} isFloat - Whether the value is a float, if true, the stepsize 0.1 and 0.01 will be used.
 * @returns 
 */
export function useNumberInputHotkeys(proxyValue, isFloat = false) {

    const onKeydown = (event) => {
        let step = 1;
        if(event.shiftKey) {
            step = 10;
        } else if(event.ctrlKey && isFloat) {
            step = 0.1;
        } else if(event.altKey && isFloat) {
            step = 0.01;
        }

        const value = parseFloat(proxyValue.value) || 0;
        if(event.key === 'ArrowUp') {
            proxyValue.value = parseFloat((value + step).toFixed(2));
            event.preventDefault();
        } else if(event.key === 'ArrowDown') {
            proxyValue.value = parseFloat((value - step).toFixed(2));
            event.preventDefault();
        }
    };

    return {
        onKeydown
    };
}
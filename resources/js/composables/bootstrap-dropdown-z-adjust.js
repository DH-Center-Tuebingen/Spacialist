import { onMounted, onBeforeUnmount, nextTick } from 'vue';

/**
 * When multiple dropdowns are used in a row, the z-index of the dropdowns can be adjusted to avoid overlapping issues.
 * @param {Object} rootElementRef - The root element ref which will be queried for all dropdowns. This is usually the component's root element.
 * @param {string} closest - The closest parent selector to find the z-index from. Default is '.col'.
 * @param {number} zIndex - The z-index value to set for the dropdowns. Default is 1.
*/
export const useBootstrapDropdownZAdjust = (rootElementRef, closest = '.col', zIndex = 1) => {

    const selectAllDropdowns = () => {
        return rootElementRef.value.querySelectorAll('[data-bs-toggle]');
    }

    const showBsDropdownHandler = (event) => {
        const target = event.target
        const parent = target.closest(closest)
        if(parent) {
            parent.style.zIndex = zIndex
        }
    }

    const hideBsDropdownHandler = (event) => {
        const target = event.target
        const parent = target.closest(closest)
        if(parent) {
            parent.style.zIndex = ""
        }
    }

    // We need to use the Map object to use a
    // DOM node as key element.
    const showBsDropdownMap = new Map()
    const hideBsDropdownMap = new Map()

    const updateAllDropdowns = () => {
        const dropdowns = selectAllDropdowns();
        dropdowns.forEach(dropdown => {
            if(!showBsDropdownMap.has(dropdown)) {
                dropdown.addEventListener('show.bs.dropdown', showBsDropdownHandler);
                showBsDropdownMap.set(dropdown, showBsDropdownHandler)
            } else {
                console.warn("Could not instantiate the zAdjust fix to element:", dropdown)
            }

            if(!hideBsDropdownMap.has(dropdown)) {
                dropdown.addEventListener('hide.bs.dropdown', hideBsDropdownHandler);
                hideBsDropdownMap.set(dropdown, hideBsDropdownHandler)
            } else {
                console.warn("Could not instantiate the zAdjust fix to element:", dropdown)
            }
        });
    }

    onMounted(() => {
        nextTick(() => {
            updateAllDropdowns();
        })
    });

    onBeforeUnmount(() => {
        showBsDropdownHandler.forEach((value, key) => value.removeEventListener(key))
        hideBsDropdownHandler.forEach((value, key) => value.removeEventListener(key))
    });

    return {
        updateAllDropdowns,
    }
}
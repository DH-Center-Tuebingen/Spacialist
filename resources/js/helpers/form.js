import { onBeforeUnmount, onMounted } from 'vue';


/**
 * A custom hook to prevent navigation when a form is dirty.
 * 
 * @param {function} func - Takes a function that retuens a truthy value if the form is dirty. 
 * Note it is a function so that it can be reactive (otherwise we could not track structures like 
 * e.g. state.dirty).
 */
export function usePreventNavigation(func) {

    const msgHeader = 'usePreventNavigation: ';

    // Keep this here until we know this is working properly.
    // Managing unbeforereload with vue and router is a bit tricky.
    const debug = false;
    const debugLog = msg => console.trace(
        `%c${msgHeader}${msg}`,
        'background: #3999ed; color: #f2f2f2; padding: 2px 6px; border-radius: 3px;'
    );

    let preventNavigation = (function(e) {
        if(debug) {
            debugLog('preventNavigation');
        }

        if(func()) {
            e.preventDefault();
            e.returnValue = '';
        }
    }).bind(this);

    const add = _ => {
        if(debug) {
            debugLog('add');
        }
        window.addEventListener('beforeunload', preventNavigation);
    };
    const remove = _ => {
        if(debug) {
            debugLog('remove');
        }

        window.removeEventListener('beforeunload', preventNavigation);
    };

    onMounted(add);
    onBeforeUnmount(remove);
}
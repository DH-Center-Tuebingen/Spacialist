
import { onMounted, reactive, ref, watch } from 'vue';

function resetToDefault(storage, defaultValue) {
    return Object.assign(storage, defaultValue);
}

function isRef(value) {
    return (typeof value !== 'object');
}

function getRefValue(value) {
    return isRef(value) ? ref(value) : reactive(value);
}

export function useLocalStorage(name, defaultValue = {}) {
    const _isRef = isRef(defaultValue);

    let loaded = false;
    const storage = getRefValue(defaultValue);

    function load() {
        const data = localStorage.getItem(name);
        let parsedData;
        if(data) {
            try {
                parsedData = JSON.parse(data);

                // This will apply the stored value to the stored value
                // and is useful if the default value changes, it should 
                // remain functional, as the default value should 'dictate'
                // the structure of the stored value.


                if(!_isRef) {
                    parsedData = { ...defaultValue, ...parsedData };
                }
            } catch(e) {
                parsedData = defaultValue;
            }
        } else {
            parsedData = defaultValue;
        }

        if(_isRef) {
            storage.value = parsedData.value;
        } else {
            Object.assign(storage, parsedData);
        }
    }

    onMounted(() => {
        load();
        loaded = true;
    });

    function save() {
        let stored = storage;
        if(storage?.__v_isRef) {
            stored = { isRef: true, value: storage.value };
        }

        localStorage.setItem(name, JSON.stringify(stored));
    }

    function reset() {
        localStorage.removeItem(name);
        resetToDefault(storage, defaultValue);
    }


    watch(storage, _ => {
        if(loaded) {
            save();
        }
    });


    return {
        load,
        save,
        reset,
        value: storage,
    };
}

export function useOptionalLocalStorage(use, name, defaultValue = {}, ...args) {
    if(use) {
        return useLocalStorage(name, defaultValue, ...args);
    } else {
        const value = getRefValue(defaultValue);
        return {
            load: _ => { },
            save: _ => { },
            reset: _ => resetToDefault(value, defaultValue),
            value
        };
    }
}
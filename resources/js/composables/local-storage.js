
import { onMounted, reactive, ref, watch } from 'vue';

function resetToDefault(storage, defaultValue) {
    return Object.assign(storage, defaultValue);
}

export function useLocalStorage(name, defaultValue = {}) {


    let loaded = false;
    const storage = isRef() ? ref(defaultValue) : reactive(defaultValue);

    function isRef() {
        return (typeof defaultValue !== 'object');
    }

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


                if(!isRef()) {
                    console.log('Merging', defaultValue, parsedData);
                    parsedData = { ...defaultValue, ...parsedData };
                }
            } catch(e) {
                parsedData = defaultValue;
            }
        } else {
            parsedData = defaultValue;
        }

        if(isRef()) {
            storage.value = parsedData.value;
        } else {
            Object.assign(storage, parsedData);
            if(parsedData.delimiter)
                console.log('DELIMITER LOADED', parsedData.delimiter);
        }
    }

    onMounted(() => {
        load();
        console.log('Loaded ' + name, storage);
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
        const value = reactive(defaultValue);
        return {
            load: _ => { },
            save: _ => { },
            reset: _ => resetToDefault(value, defaultValue),
            value
        };
    }
}
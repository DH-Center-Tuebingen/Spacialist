import {
    createVNode,
    inject,
    provide,
    render,
} from 'vue';

import {
    Toast,
} from 'bootstrap';

import ToastComponent from '../components/plugins/Toast.vue';

const store = {
    wrapper: null,
};

const defaultConfig = {
    duration: 5000,
    autohide: true,
    channel: 'default',
    icon: false,
    container: 'toast-container',
    is_tag: true,
};

const perToastConfig = ['duration', 'autohide', 'channel', 'icon'];

function addToast(message, title, config) {
    const toastId = `toast-${(new Date()).getTime()}`;
    const toastContainer = document.createElement('div');
    render(createVNode(ToastComponent, {
        message: message,
        title: title,
        id: toastId,
        ...config,
    }), toastContainer);
    store.wrapper.appendChild(toastContainer);
    const toastElem = document.getElementById(toastId);
    toastElem.addEventListener('hidden.bs.toast', e => {
        e.target.parentNode.remove();
    });
    const bsToast = new Toast(toastElem);
    bsToast.show();
};

function initializeWrapper(config) {
    let wrapper;
    if(config.is_tag) {
        const containers = document.getElementsByTagName(config.container);
        if(!containers || containers.length < 1) {
            throw new Error(`Could not find elements with tag ${config.container} in DOM!`);
        }
        wrapper = containers[0];
        const toastContainer = document.createElement('div');
        toastContainer.classList.add(['toast-container']);
        wrapper.appendChild(toastContainer);
    } else {
        wrapper = document.getElementById(config.container);
        if(!wrapper) {
            throw new Error(`Could not find element with ID ${config.container} in DOM!`);
        }
    }
    store.wrapper = wrapper;
};

function reduceConfig(config, allowed) {
    return Object.keys(config)
        .filter(key => allowed.includes(key))
        .reduce((obj, key) => {
            return {
            ...obj,
            [key]: config[key]
            };
        }, {});
}

function reduceGlobalConfig(config) {
    return reduceConfig(config, Object.keys(defaultConfig));
};

function reduceToastConfig(config) {
    return reduceConfig(config, perToastConfig);
};

const createToast = config => ({
    config: config,
    $toast(message, title, config = {}) {
        const reducedLocal = reduceToastConfig(config);
        const combined = reduceToastConfig({...this.config, ...reducedLocal});
        addToast(message, title, combined);
    },
});

const toastSymbol = Symbol();

export function provideToast(toastConfig = {}) {
    const reducedConfig = reduceGlobalConfig({...defaultConfig, ...toastConfig});
    const toast = createToast(reducedConfig);
    provide(toastSymbol, toast);
}

export function useToast() {
    const toast = inject(toastSymbol);
    if (!toast) throw new Error("No toast provided!");

    if(!store.wrapper) {
        initializeWrapper(toast.config);
    }

    return toast;
}
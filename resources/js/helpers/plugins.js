export const appendScript = location => {
    const scriptTag = document.createElement('script');
    scriptTag.type = 'text/javascript';
    scriptTag.src = `storage/${location}`;
    document.body.appendChild(scriptTag);
};

export const removeScript = location => {
    const scripts = [
        ...document.body.getElementsByTagName('script')
    ];
    const oldScript = scripts.find(s => s.src.endsWith(location));
    if(oldScript) {
        document.body.removeChild(oldScript);
    }
};

export const isSlotValid = name => {
    return availableSlots().includes(name);
};

export const validateOrigin = options => {
    if(!options.of || !SpPS.data.plugins[options.of]) {
        throw new Error('This plugin part has no associated plugin or that plugin is not installed!');
    }
};

export const validateSubscription = (options) => {
    validateOrigin(options);

    if(!options.topic) {
        throw new Error('No topic for subscription provided!');
    }

    if(!availabelSubscriptions().includes(options.topic)) {
        throw new Error('Invalid topic for subscription provided: ' + options.topic);
    }
    
    if(!options.update || typeof options.update !== 'function') {
        throw new Error('No update function for subscription provided!');
    }
    
    if(!options.components || typeof options.components !== 'object') {
        throw new Error('No components function for subscription provided!');
    }
};

export const availableSlots = () => {
    return [
        'tab',
        'tools',
        'settings',
        'dataModelOptions',
    ];
};

export const availabelSubscriptions = () => {
    return [
        'entityDetail',
    ];
};
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

export const availableSlots = () => {
    return [
        'tab',
        'tools',
        'settings',
        'entityDetailTab',
        'dataModelOptions',
    ];
};
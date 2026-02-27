export const appendScript = location => {
    const scriptTag = document.createElement('script');
    scriptTag.type = 'text/javascript';
    scriptTag.src = `api/download/plugin/${location}`;
    document.head.appendChild(scriptTag);
};

export const removeScript = location => {
    const scripts = [
        ...document.head.getElementsByTagName('script')
    ];
    const oldScript = scripts.find(s => s.src == `/download/plugin?src=${location}`);
    if(oldScript) {
        document.head.removeChild(oldScript);
    }
};

export const isInstalled = (plugin) => {
    return plugin?.installed_at !== null;
}

export const getPluginTitle = (plugin) => {
    return plugin.metadata?.title || plugin.name;
}
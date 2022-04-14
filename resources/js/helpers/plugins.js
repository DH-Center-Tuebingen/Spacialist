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
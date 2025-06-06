export const appendScript = location => {
    const scriptTag = document.createElement('script');
    scriptTag.type = 'text/javascript';
    scriptTag.src = `download/plugin?src=${location}`;
    document.head.appendChild(scriptTag);
};

export const removeScript = location => {
    const scripts = [
        ...document.head.getElementsByTagName('script')
    ];
    const oldScript = scripts.find(s => s.src.endsWith(location));
    if(oldScript) {
        document.head.removeChild(oldScript);
    }
};
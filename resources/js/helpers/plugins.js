export const appendScriptsAndStyles = response => {
    const { scripts, styles } = response;

    if(scripts) {
        appendScripts(scripts);
    }else {
        console.warn("No scripts to append for plugin");
    }
    
    if(styles) {
        appendStyles(styles);
    }else {
        console.warn("No styles to append for plugin");
    }
}

export const appendStyle = location => {
    console.log('Appending style with location', location);
    const linkTag = document.createElement('link');
    linkTag.rel = 'stylesheet';
    linkTag.href = `${location}`;
    document.head.appendChild(linkTag);
}

export const appendStyles = locations => {
    locations.forEach(location => {
        appendStyle(location);
    });
}

export const appendScript = location => {
    console.log('Appending script with location', location);
    const scriptTag = document.createElement('script');
    scriptTag.type = 'text/javascript';
    scriptTag.src = `${location}`;
    document.head.appendChild(scriptTag);
};

export const appendScripts = locations => {
    locations.forEach(location => {
        appendScript(location);
    });
}

export const removeScriptsAndStyles = response => {
    const { scripts, styles } = response;
    removeScripts(scripts);
    removeStyles(styles);
}

export const removeStyles = locations => {
    locations.forEach(location => {
        removeStyle(location);
    });
}

export const removeStyle = location => {
    const links = [
        ...document.head.getElementsByTagName('link')
    ];
    const oldLink = links.find(l => l.href == `/download/plugin?src=${location}`);
    if(oldLink) {
        document.head.removeChild(oldLink);
    }
}

export const removeScripts = locations => {
    locations.forEach(location => {
        removeScript(location);
    });
}

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
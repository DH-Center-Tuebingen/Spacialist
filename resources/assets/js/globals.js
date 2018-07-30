// Validators
Vue.prototype.$validateObject = function(value) {
    // concepts is valid if it is either an object
    // or an empty array
    // (empty assoc arrays are simple arrays in php)
    return typeof value == 'object' || (typeof value == 'array' && value.length == 0);
};

// Directives
Vue.directive('can', {
    terminal: true,
    bind: function(el, bindings) {
        const canI = this.Vue.prototype.$can(bindings.value, bindings.modifiers.one);

        if(!canI) {
            this.warning = document.createElement('p');
            this.warning.className = 'alert alert-warning v-can-warning';
            this.warning.innerHTML = 'You do not have permission to access this page';
            for(let i=0; i<el.children.length; i++) {
                let c = el.children[i];
                c.classList.add('v-can-hidden');
            }
            el.appendChild(this.warning);
        }
    },
    unbind: function(el) {
        if(!el.children) return;
        for(let i=0; i<el.children.length; i++) {
            let c = el.children[i];
            // remove our warning elem
            if(c.classList.contains('v-can-warning')) {
                el.removeChild(c);
                continue;
            }
            if(c.classList.contains('v-can-hidden')) {
                c.classList.remove('v-can-hidden');
            }
        }
    }
});

// Prototype
Vue.prototype.$can = function(permissionString, oneOf) {
    oneOf = oneOf || false;
    const user = this.$auth.user();
    if(!user) return false;
    const permissions = permissionString.split('|');
    const hasPermission = function(permission) {
        return user.permissions[permission] === 1;
    };

    if(oneOf) {
        return permissions.some(hasPermission);
    } else {
        return permissions.every(hasPermission);
    }
}

Vue.prototype.$showToast = function(title, text, type, duration) {
    type = type || 'info'; // success, info, warn, error
    duration = duration || 2000;
    this.$notify({
        group: 'spacialist',
        title: title,
        text: text,
        type: type,
        duration: duration
    });
};

Vue.prototype.$throwError = function(error) {
    if(error.response) {
        const r = error.response;
        const req = {
            status: r.status,
            url: r.config.url,
            method: r.config.method.toUpperCase()
        };
        this.$showErrorModal(r.data, r.headers, req);
    } else if(error.request) {
        this.$showErrorModal(error.request);
    } else {
        this.$showErrorModal(error.message);
    }
};

Vue.prototype.$showErrorModal = function(errorMsg, headers, request) {
    this.$modal.show('error-modal', {msg: errorMsg, headers: headers, request: request});
};

Vue.prototype.$createDownloadLink = function(content, filename, base64, contentType) {
    base64 = base64 || false;
    var link = document.createElement("a");
    let url;
    if(base64) {
        contentType = contentType || 'text/plain';
        url = `data:${contentType};base64,${content}`;
    } else {
        url = window.URL.createObjectURL(new Blob([content]));
    }
    // link.setAttribute("href", 'data:;base64,' + raw);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
}

Vue.prototype.$hasConcept = function(url) {
    if(!url) return false;
    return !!this.$root.$data.concepts[url];
}

Vue.prototype.$translateConcept = function(url) {
    const concepts = this.$root.$data.concepts;
    if(!url || !concepts) return url;
    if(!concepts[url]) return url;
    return concepts[url].label;
}

Vue.prototype.$getEntityType = function(id) {
    return this.$root.$data.contextTypes[id];
}

Vue.prototype.$getEntityTypes = function() {
    return this.$root.$data.contextTypes;
}

Vue.prototype.$getPreference = function(pref) {
    return this.$root.$data.preferences[pref];
}

Vue.prototype.$getTabPlugins = function() {
    return this.$root.$data.plugins.tab;
}

Vue.prototype.$getToolPlugins = function() {
    return this.$root.$data.plugins.tools;
}

Vue.prototype.$getSettingsPlugins = function() {
    return this.$root.$data.plugins.settings;
}

Vue.prototype.$getPlugins = function() {
    return this.$root.$data.plugins;
}

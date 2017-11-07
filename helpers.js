function swap(a, i, j) {
    var t = a[i];
    a[i] = a[j];
    a[j] = t;
}

function getCurrentDeviceClass() {
    return $('#current-device-class').find('div:visible').first().attr('id');
}

function nextMatchingDeviceClass(col, currentClass) {
    var availableClasses = ['xs', 'sm', 'md', 'lg'];
    var indexedClasses = {
        xs: undefined,
        sm: undefined,
        md: undefined,
        lg: undefined
    };
    var regex = /col-([^-]*)-\d*/gi;
    var matches = regex.exec(col);
    while(matches !== null) {
        var m = matches[1];
        indexedClasses[m] = 1;
        matches = regex.exec(col);
    }
    if(indexedClasses[currentClass]) return currentClass;
    var startIndex = availableClasses.indexOf(currentClass);
    for(var i=startIndex; i>=0; i--) {
        if(indexedClasses[availableClasses[i]]) return availableClasses[i];
    }
    return null;
}

function createDownloadLink(raw, filename) {
    var link = document.createElement("a");
    link.setAttribute("href", 'data:;base64,' + raw);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
}

// Convert context' last modification (created or updated) to a locale representation
function updateLastModified(context) {
    var lastmodified = context.updated_at || context.created_at;
    var d = new Date(lastmodified);
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
}

L.Control.FitWorld = L.Control.extend({
	onAdd: function(map) {
        var o = this.options;
        o.onClick = o.onClick || function() {};
        var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-fitworld');
        var elem = L.DomUtil.create('a', 'leaflet-control-fitworld-button', container);
        var icon = L.DomUtil.create('i', 'material-icons md-18', elem);
        icon.innerHTML = 'zoom_out_map';
        elem['ui-sref'] = '';
        elem.role = 'button';

        container.onclick = function(){
            o.onClick();
        };
        return container;
	},

	onRemove: function(map) {
	}
});

L.control.fitworld = function(opts) {
	return new L.Control.FitWorld(opts);
};

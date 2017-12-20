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

function getElementHeight(elem) {
    var style = getComputedStyle(elem);
    var height = elem.offsetHeight;
    var margin = parseInt(style.marginTop) + parseInt(style.marginBottom);
    var padding = parseInt(style.paddingTop) + parseInt(style.paddingBottom);
    return height + margin + padding;
}

// Convert context' last modification (created or updated) to a locale representation
function updateLastModified(context) {
    var lastmodified = context.updated_at || context.created_at;
    var d = new Date(lastmodified);
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
}

function resetObject(o) {
    for(var k in o) {
        if(o.hasOwnProperty(k)) {
            delete o[k];
        }
    }
}

function createCountingCsvHeader(cnt, translate) {
    var columns = [];
    for(var i=0; i<cnt; i++) {
        var c = translate.instant('csv.header.unnamed-column', {col: i+1});
        columns.push(c);
    }
    return columns;
}

function showLoadingOverlay(msg) {
    $('#loadingUiMessage').text(msg);
    $('#loadingUiWrapper').show();
}

function hideLoadingOverlay() {
    $('#loadingUiMessage').text('');
    $('#loadingUiWrapper').hide();
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
        elem.title = 'Fit map to all features';

        L.DomEvent.on(container, 'dblclick', L.DomEvent.stopPropagation);

        container.onclick = function() {
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

L.Control.ToggleMeasurements = L.Control.extend({
	onAdd: function(map) {
        var state = false;
        var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-toggle-measurements');
        var elem = L.DomUtil.create('a', 'leaflet-control-toggle-measurements-button', container);
        var icon = L.DomUtil.create('i', 'material-icons md-18', elem);
        icon.innerHTML = 'straighten';
        elem['ui-sref'] = '';
        elem.role = 'button';
        elem.title = 'Toggle Measurements';

        L.DomEvent.on(container, 'dblclick', L.DomEvent.stopPropagation);

        container.onclick = function() {
            map.eachLayer(function(l) {
                if(l.feature && l.feature.geometry.type != 'Point') {
                    if(state) {
                        l.hideMeasurements();
                    } else {
                        l.showMeasurements();
                    }
                }
            });
            state = !state;
        }
        return container;
	},

	onRemove: function(map) {
	}
});

L.control.togglemeasurements = function(opts) {
	return new L.Control.ToggleMeasurements(opts);
};

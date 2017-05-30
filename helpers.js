Array.prototype.swap = function(a, b) {
    var t = this[a];
    this[a] = this[b];
    this[b] = t;
    return this;
};

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

spacialistApp.controller('subTreeCtrl', ['$scope', 'scopeService', 'httpPostFactory', 'httpGetFactory', 'storeContext', '$q', function($scope, scopeService, httpPostFactory, httpGetFactory, storeContext, $q) {
    if(typeof $scope.ctxts === 'undefined') $scope.ctxts = scopeService.ctxts;
    if(typeof $scope.ctxtRefs === 'undefined') $scope.ctxtRefs = scopeService.ctxtRefs;
    if(typeof this.fields === 'undefined') this.fields = scopeService.fields;
    if(typeof $scope.type === 'undefined') $scope.type = scopeService.cType;
    if(typeof $scope.choices === 'undefined') $scope.choices = scopeService.choices;
    if(typeof $scope.artifacts === 'undefined') $scope.artifacts = scopeService.artifacts;
    if(typeof $scope.artiRefs === 'undefined') $scope.artiRefs = scopeService.artiRefs;
    if(typeof $scope.artiFields === 'undefined') $scope.artiFields = scopeService.artiFields;
    if(typeof $scope.aType === 'undefined') $scope.aType = scopeService.aType;

    // In the harris view tab you can create new elements ('new') or edit the currently selected ('edit') element. They share the same attribute arrays, thus an identifier is needed
    var attribDataTypes = ['new', 'edit'];
    $scope.contextData = new FormData();
    $scope.attribData = new FormData();

    /*
     * callback for context-sc elements
     */
    this.onSelectCallback = function(item, model) {
        scopeService.cType = model;
        this.fields = $scope.ctxtRefs[model];
        scopeService.fields = this.fields;
        resetValues();
        $scope.list = [];
    }

    /*
     * The callback which gets called if the user selects a context or artifact from the list
     * It gets the input fields for this particular context/artifact, which can then be displayed
     */
    this.onSelectElementCallback = function(item, model, isCtx) {
        var attrDT = attribDataTypes[0];
        if(isCtx) {
            $scope.typeFields = $scope.ctxtRefs[model.index];
        } else {
            $scope.typeFields = $scope.artiRefs[model.index];
        }
        scopeService.typeFields = $scope.typeFields;
        $scope.attribData[attrDT] = {
            typeId: model.type,
            typeName: model.title,
            typeIndex: model.index
        };
    }

    /*
     * callback for context-mc elements
     */
    this.onAddToList = function(item, model) {
        $scope.choices.push(item);
        scopeService.choices = $scope.choices;
    }

    /*
     * callback for context-mc elements
     */
    this.onRemoveFromList = function(item, model) {
        var idx = $scope.choices.indexOf(item);
        if(idx !== -1) $scope.choices.splice(idx, 1);
        scopeService.choices = $scope.choices;
    }

    $scope.open = function() {
        $scope.date.opened = true;
    }

    $scope.getCurrentDate = function(dt) {
        console.log("Set date to " + dt.toString())
    }

    /*
     * Resets the add context/artifact divs
     */
    var resetDivs = function() {
        $scope.addContext = false;
        $scope.addArtifact = false;
        $scope.selection = [];
        $scope.attribData[attribDataTypes[0]] = new FormData();
    }

    /**
     * Opens the div for adding a new context
     */
    $scope.openContextDiv = function() {
        $scope.addContext = true;
        $scope.addArtifact = false;
        $scope.selection = $scope.ctxts.slice();
    }

    /**
     * Opens the div for adding a new artifact
     */
    $scope.openArtifactDiv = function() {
        $scope.addArtifact = true;
        $scope.addContext = false;
        $scope.selection = $scope.artifacts.slice();
    }

    /**
     * Adds an element to the context tree and the database as well.
     * Uses the $scope.attribData array with the index `attrDT`
     * It also redraws the harris matrix visualization
     */
    $scope.saveElement = function(attrDT) {
        var attribData = $scope.attribData[attrDT];
        if(attribData.name.length == 0) return;
        if(typeof $scope.currentContext !== 'undefined') {
            var parent = $scope.currentContext;
        } else {
            var parent = $scope.sideNav.contextHistory[0];
        }
        var elem = {
            title: attribData.name,
            clickable: true,
            typeId: attribData.typeId,
            typeName: attribData.typeName,
            typeIndex: attribData.typeIndex,
            contextType: $scope.typeFields[0].context,
            data: []
        };
        delete attribData.typeId;
        delete attribData.typeName;
        delete attribData.typeIndex;
        angular.forEach(attribData, function(value, key) {
            var attr = {};
            if(key != 'name') {
                var ids = key.split('_');
                attr.context = ids[0];
                attr.attr = ids[1];
                attr.value = value;
                elem.data.push(attr);
            }
        });
        elem.id = $scope.sideNav.contextIds++;
        if(elem.typeId == 0) {
            elem.fields = $scope.ctxtRefs[elem.typeIndex].slice();
            elem.children = [];
        } else if(elem.typeId == 1) {
            elem.fields = $scope.artiRefs[elem.typeIndex].slice();
        }
        elem.realId = -1;
        elem.parentId = parent.realId;
        var promise = storeElement(elem);
        promise.then(function(newRealId) {
            elem.realId = newRealId;
            parent.children.push(elem);
            $scope.createGraphFromHistory();
        });
        resetValues();
    }

    /**
     * Stores the whole context tree in the database
     */
    $scope.storeSubElements = function() {
        var root = $scope.sideNav.contextHistory[0];
        storeSubElements(root);
        $scope.updateInformations();
    }

    /**
     * Stores all children of a given element `root` of the context tree in the database
     */
    var storeSubElements = function(root) {
        var parentId = root.realId;
        if(typeof root.children === 'undefined') return;
        for(var i=0; i<root.children.length; i++) {
            var child = root.children[i];
            if(child.typeId == 1) { //find
                var promise = storeFind(child, parentId);
                promise.then(function(newRealId) {
                    child.realId = newRealId;
                });
            } else if(child.typeId == 0) { //context
                var promise = storeContexti(child, parentId);
                promise.then(function(newRealId) {
                    storeSubElements(child);
                });
            }
        }
    }

    /**
     * Stores a single element of the context tree in the database
     */
    var storeElement = function(elem) {
        var parent = elem.parentId;
        if(elem.typeId == 0) {
            var promise = storeContexti(elem, parent);
        } else if(elem.typeId == 1) {
            var promise = storeFind(elem, parent);
        }
        return promise;
    }

    /**
     * Stores all children of a given context array `contexts` and their parent `parent`
     */
    var storeContexts = function(contexts, parent) {
        for(var i=0; i<contexts.length; i++) {
            var ctx = contexts[i];
            var promise = storeContexti(ctx, parent);
            promise.then(function(parentId) {
                storeFinds(ctx.children[1].children, parentId);
                storeContexts(ctx.children[0].children, parentId);
            });
        }
    }

    /**
     * Stores the given context `context` with the given parent `parent` in the database.
     * @return: returns a promise which returns the ID of the newly inserted context
     */
    var storeContexti = function(context, parent) {
            var formData = new FormData();
            formData.append('name', context.title);
            formData.append('root', parent);
            formData.append('cid', context.contextType);
            if(typeof context.realId !== 'undefined' && context.realId != -1) {
                formData.append('realId', context.realId);
            }
            for(var i=0; i<context.data.length; i++) {
                var dat = context.data[i];
                formData.append(dat.attr, dat.value);
            }
            var promise = storeContext.getData('../spacialist_api/context/set', formData);
            return promise;
    }

    /**
     * Stores all elements of the given find array `finds` and their parent `parent`
     */
    var storeFinds = function(finds, parent) {
        for(var i=0; i<finds.length; i++) {
            var promise = storeFind(finds[i], parent);
            promise.then(function(realId) {
                finds[i].realId = realId;
            })
        }
    }

    /**
     * Stores the given find `f` with the given parent `parent` in the database.
     * @return: returns a promise which returns the ID of the newly inserted find
     */
    var storeFind = function(f, parent) {
            var formData = new FormData();
            formData.append('name', f.title);
            formData.append('root', parent);
            formData.append('cid', f.contextType);
            if(typeof f.realId !== 'undefined' && f.realId != -1) {
                formData.append('realId', f.realId);
            }
            for(var i=0; i<f.data.length; i++) {
                var dat = f.data[i];
                formData.append(dat.attr, dat.value);
            }
            var promise = storeContext.getData('../spacialist_api/context/set', formData);
            return promise;
    }

    $scope.cancelArtifact = function() {
        resetValues();
    }

    $scope.removeArtifact = function(index) {
        console.log("Remove artifact @ " + index);
        if($scope.activeIndex == index) {
            resetValues();
            $scope.aType = scopeService.aType = "";
        }
        $scope.list.splice(index, 1);
    }

    /**
     * Helper function to delete the context with the given id `id` from the given (sub)-tree `curr`.
     @return: returns the id of the context which has been removed
     */
    var deleteElement = function(curr, id) {
        if(curr.realId == id) return id;
        if(typeof curr.children === 'undefined') return -1;
        for(var i=0; i<curr.children.length; i++) {
            var value = curr.children[i];
            if(value.realId == id) {
                curr.children.splice(i, 1);
                return id;
            }
            var foundId = deleteElement(value, id);
            if(foundId >= 0) return foundId;
        }
        return -1;
    }

    /**
     * Deletes the current context from the the context tree and the DB as well.
     */
    $scope.deleteElement = function() {
        var curr = $scope.sideNav.contextHistory[0];
        var id = $scope.currentContext.realId;
        var foundId = deleteElement(curr, id);
        console.log(foundId);
        if(foundId >= 0) {
            httpPostFactory('../spacialist_api/context/delete/'+foundId, function(callback) {
            });
        }
        $scope.currentContext = curr;
    }

    $scope.updateElement = function() {
        var attribData = $scope.attribData[attribDataTypes[1]];
        var data = [];
        $scope.currentContext.title = attribData.name;
        angular.forEach(attribData, function(value, key) {
            if(key != 'name') {
                var attr = {};
                var ids = key.split('_');
                attr.context = ids[0];
                attr.attr = ids[1];
                attr.value = value;
                data.push(attr);
            }
        });
        $scope.currentContext.data = data;
        $scope.storeSubElements();
    }

    /**
     * Computes the number of child elements for a given element `parent`
     * @returns the number of children
     */
    $scope.getRecLength = function(parent) {
        var sum = 0;
        if(typeof parent === 'undefined' || typeof parent.children === 'undefined') return sum;
        if(typeof parent.children[0] !== 'undefined') {
            if(typeof parent.children[0].children !== 'undefined') {
                sum += parent.children[0].children.length;
                for(var i=0; i<parent.children[0].children.length; i++) {
                    var children = parent.children[0].children[i];
                    sum += $scope.getRecLength(children);
                }
            }
        }
        if(typeof parent.children[1] !== 'undefined') {
            if(typeof parent.children[1].children !== 'undefined') {
                sum += parent.children[1].children.length;
            }
        }
        return sum;
    }

    /**
     * Set the current context (`$scope.currentContext`) to the given element `parent`. Stores the `parent`'s values in the `$scope.attribData['edit']` array.
     */
    $scope.setEditContext = function(parent) {
        var attrDT;
        $scope.currentContext = parent;
        $scope.attribDataType = attrDT = attribDataTypes[1];
        $scope.attribData[attrDT] = {};
        $scope.attribData[attrDT]['name'] = parent.title;
        angular.forEach(parent.data, function(value, key) {
            var index = value.context + "_" + value.attr;
            $scope.attribData[attrDT][index] = value.value;
        });
        $scope.stuffNav.setEditTab();
    }

    /**
     * @returns json object with color settings for different context types, based on their id `id`.
     * Colors are in hsl format with fixed saturation and lightness. Hue is computed based on the `id`'s hash.
     */
    $scope.getColorForId = function(id) {
        var sat = '90%';
        var lgt = '65%';
        if(typeof id === 'undefined') {
            var hue = 0;
        } else {
            //TODO hacky way to get different colors
            var newId = window.btoa(id);
            var newerId = newId;
            for(var i=0; i<newId.length; i++) {
                newerId += newId.charCodeAt(i);
            }
            var hue = getHashCode(newerId) % 360;
        }
        var hsl = [
            hue,
            sat,
            lgt
        ];
        return {
            'color': 'hsl(' + hsl.join(',') + ')'
        };
    }

    /**
     * @returns hash code for a given string `str`
     */
    var getHashCode = function(str) {
        var hash = 0;
        if(str.length === 0) return hash;
        for(var i=0; i<str.length; i++) {
            var chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0;
        }
        return hash;
    }

    /**
     * Closes an alert window based on the close button's event object `event`
     */
    $scope.closeAlert = function(event) {
        var id = event.target.parentNode.id;
        var elem = document.getElementById(id);
        elem.parentNode.removeChild(elem);
        $scope.closedAlerts[id] = true;
    }

    var resetValues = function() {
        $scope.activeIndex = -1;
        resetDivs();
    }
}]);

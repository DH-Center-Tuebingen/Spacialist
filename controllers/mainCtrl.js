spacialistApp.controller('mainCtrl', ['$scope', 'scopeService', 'httpPostFactory', 'httpGetFactory', 'httpPostPromise', 'modalService', '$uibModal', '$auth', '$state', '$http', 'modalFactory', 'moduleHelper', function($scope, scopeService, httpPostFactory, httpGetFactory, httpPostPromise, modalService, $uibModal, $auth, $state, $http, modalFactory, moduleHelper) {
    var createModalHelper = function($itemScope, elemType) {
        var parent = $itemScope.parent;
        var selection = [];
        var msg = '';
        if(elemType == 'context') {
            selection = $scope.ctxts.slice();
            msg = 'Neuen Kontext anlegen';
        } else if(elemType == 'find') {
            selection = $scope.artifacts.slice();
            msg = 'Neuen Fund anlegen';
        }
        modalFactory.createModal(parent.name, msg, selection, function(name, type) {
            var elem = {
                name: name,
                c_id: type.cid,
                root: parent.id,
                reclevel: parent.reclevel + 1,
                typeid: type.type,
                typename: type.index,
                typelabel: type.title,
                data: [],
                children: []
            };
            var formData = new FormData();
            formData.append('name', name);
            formData.append('cid', type.cid);
            formData.append('root', parent.id);
            httpPostFactory('../spacialist_api/context/set', formData, function(newElem) {
                elem.id = newElem.fid;
                parent.children.push(elem);
                $scope.setCurrentElement(elem, $scope.currentElement);
                $itemScope.expand();
            });
        });
    };

    $scope.newElementContextMenu = [
        [
            function($itemScope, $event, modelValue, text, $li) {
                return 'Optionen für ' + $itemScope.parent.name;
            },
            function($itemScope, $event, modelValue, text, $li) {
            },
            function() { return false; }
        ],
        null,
        ['<span class="fa fa-fw fa-plus-circle fa-light fa-green"></span> Neuer Fund', function($itemScope, $event, modelValue, text, $li) {
            createModalHelper($itemScope, 'find');
        }],
        ['<span class="fa fa-fw fa-plus-circle fa-light fa-green"></span> Neuer Kontext', function($itemScope, $event, modelValue, text, $li) {
            createModalHelper($itemScope, 'context');
        }],
        null,
        ['<span class="fa fa-fw fa-trash-o fa-light fa-red"></span> Löschen', function($itemScope, $event, modelValue, text, $li) {
            modalFactory.deleteModal($itemScope.parent.name, function() {
                $scope.deleteElement($itemScope.parent, function() {
                    $itemScope.remove();
                });
            });
        }]
    ];

    $scope.layerTwo = {
        activeTab: 'map'
    };

    $scope.setActiveTab = function(tabId) {
        $scope.layerTwo.activeTab = tabId;
    };

    $scope.getContextList = function() {
        $scope.getContextListStarted = true;
        $scope.testingElement = {};
        httpGetFactory('../spacialist_api/context/getRecursive', function(contextList) {
            $scope.contextList = contextList;
            $scope.getContextListStarted = false;
        });
    };

    $scope.setCurrentElement = function(target, elem) {
        console.log(target);
        if(typeof elem != 'undefined' && elem.id == target.id) {
            $scope.currentElementData = undefined;
            $scope.currentElementFields = undefined;
            $scope.currentElement = undefined;
            return;
        }
        elem = target;
        if(elem.typeid === 0) { //context
            elem.fields = $scope.ctxtRefs[elem.typename].slice();
        } else if(elem.typeid == 1) { //find
            elem.fields = $scope.artiRefs[elem.typename].slice();
        }
        var data = {};
        for(var i=0; i<elem.data.length; i++) {
            var value = elem.data[i];
            //console.log(value.toSource());
            var index = value.a_id + '_' + (value.o_id || '');
            var posIndex = index + 'pos';
            var val = value.str_val;
            var dType = value.datatype;
            data[posIndex] = value.possibility || 100;
            if(dType == 'list') {
                if(typeof data[index] == 'undefined') data[index] = [];
                data[index].push({
                    name: val
                });
            } else if(dType == 'string-sc') {
                data[index] = value.val;
            } else if(dType == 'string-mc') {
                if(typeof data[index] == 'undefined') data[index] = [];
                data[index].push(value.val);
            } else if(dType == 'dimension' || dType == 'epoch') {
                if(typeof data[index] == 'undefined') data[index] = {};
                data[index].val = val;
            } else {
                data[index] = val;
            }
        }
        $scope.currentElementData = data;
        $scope.currentElementFields = elem.fields;
        $scope.currentElement = {
            id: elem.id,
            name: elem.name,
            root: elem.root,
            typeId: elem.typeid,
            cid: elem.c_id
        };
    };

    $scope.storeElement = function(elem, data) {
        console.log("Would store elem " + elem.name + " with ID " + elem.id + " in the database.");
        var parsedData = [];
        angular.forEach(data, function(value, key) {
            if(key != 'name' && !key.endsWith('pos')) {
                var attr = {};
                attr.key = key;
                attr.value = value;
                parsedData.push(attr);
            }
        });
        elem.data = parsedData;
        var promise = storeElement(elem);
        promise.then(function(newRealId){
            console.log(newRealId);
        });
    };

    /**
     * Stores a single element of the context tree in the database
     */
    var storeElement = function(elem) {
        var parentId = elem.root;
        var promise;
        if(elem.typeId === 0) {
            promise = storeContext(elem, parentId);
        } else if(elem.typeId == 1) {
            promise = storeFind(elem, parentId);
        }
        return promise;
    };

    /**
     * Stores the given context `context` with the given parent `parentId` in the database.
     * @return: returns a promise which returns the ID of the newly inserted context
     */
    var storeContext = function(context, parentId) {
        console.log("store context " + context.name);
        var formData = new FormData();
        formData.append('name', context.name);
        formData.append('root', parentId);
        formData.append('cid', context.cid);
        if(typeof context.id !== 'undefined' && context.id != -1) {
            formData.append('realId', context.id);
        }
        for(var i=0; i<context.data.length; i++) {
            var d = context.data[i];
            var currValue = '';
            if (typeof d.value === 'object') {
                currValue = angular.toJson(d.value);
            } else {
                currValue = d.value;
            }
            formData.append(d.key, currValue);
        }
        var promise = httpPostPromise.getData('../spacialist_api/context/set', formData);
        return promise;
    };

    /**
     * Stores the given find `f` with the given parent `parentId` in the database.
     * @return: returns a promise which returns the ID of the newly inserted find
     */
    var storeFind = function(f, parentId) {
        console.log("store find " + f.name);
        var formData = new FormData();
        formData.append('name', f.name);
        formData.append('root', parentId);
        formData.append('cid', f.cid);
        if(typeof f.id !== 'undefined' && f.id != -1) {
            formData.append('realId', f.id);
        }
        for(var i=0; i<f.data.length; i++) {
            var d = f.data[i];
            var currValue = '';
            if (typeof d.value === 'object') {
                currValue = angular.toJson(d.value);
            } else {
                currValue = d.value;
            }
            formData.append(d.key, currValue);
        }
        var promise = httpPostPromise.getData('../spacialist_api/context/set', formData);
        return promise;
    };

    $scope.deleteElement = function(elem, onSuccess) {
        console.log("Removing element " + elem.name + " with ID " + elem.id);
        httpGetFactory('../spacialist_api/context/delete/' + elem.id, function(callback) { onSuccess(); });
    };

    /**
     * `name` is the name of the controller which is bound to this module
     * @returns whether the given controller exists or not
     */
    $scope.moduleExists = function(name) {
        return moduleHelper.controllerExists(name);
    };

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
     * @returns json object with color settings for different context types, based on their id `id`.
     * Colors are in hsl format with fixed saturation and lightness. Hue is computed based on the `id`'s hash.
     */
    $scope.getColorForId = function(id) {
        var sat = '90%';
        var lgt = '65%';
        var hue = 0;
        if(typeof id !== 'undefined') {
            var newId = '';
            for(var i=0; i<id.length; i++) {
                newId += id.charCodeAt(i);
            }
            hue = getHashCode(newId) % 360;
        }
        var hsl = [
            hue,
            sat,
            lgt
        ];
        return {
            'color': 'hsl(' + hsl.join(',') + ')'
        };
    };
}]);

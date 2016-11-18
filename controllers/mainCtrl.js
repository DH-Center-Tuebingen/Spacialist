spacialistApp.controller('mainCtrl', ['$scope', 'scopeService', 'httpPostFactory', 'httpGetFactory', 'httpPostPromise', 'httpGetPromise', 'modalService', '$uibModal', '$auth', '$state', '$http', 'modalFactory', 'moduleHelper', function($scope, scopeService, httpPostFactory, httpGetFactory, httpPostPromise, httpGetPromise, modalService, $uibModal, $auth, $state, $http, modalFactory, moduleHelper) {
    $scope.dimensionUnits = [
        'nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'
    ];

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
        ['<span class="fa fa-fw fa-clone fa-light fa-green"></span> Kontext duplizieren', function($itemScope, $event, modelValue, text, $li) {
            console.log("Clone!");
        }],
        null,
        ['<span class="fa fa-fw fa-trash-o fa-light fa-red"></span> Löschen', function($itemScope, $event, modelValue, text, $li) {
            modalFactory.deleteModal($itemScope.parent.name, function() {
                deleteElement($itemScope.parent, function() {
                    $itemScope.remove();
                });
            }, 'Wenn Sie dieses Element löschen, werden auch alle Kind-Elemente gelöscht!');
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
            var index = value.attribute_id + '_' + (value.o_id || '');
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
            } else if(dType == 'dimension') {
                data[index] = JSON.parse(value.val);
            } else if(dType == 'epoch') {
                data[index] = JSON.parse(value.val);
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
            cid: elem.context_id
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
     * @return: returns a promise which returns the ID of the newly inserted context
     */
    var storeElement = function(elem) {
        console.log("store context " + elem.name);
        var parentId = elem.root;
        var formData = new FormData();
        formData.append('name', elem.name);
        formData.append('root', parentId);
        formData.append('cid', elem.cid);
        if(typeof elem.id !== 'undefined' && elem.id != -1) {
            formData.append('realId', elem.id);
        }
        for(var i=0; i<elem.data.length; i++) {
            var d = elem.data[i];
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

    $scope.deleteElement = function(elem) {
        modalFactory.deleteModal(elem.name, function() {
            deleteElement(elem, function() {
                //$itemScope.remove(); TODO remove from list
            });
        }, 'Wenn Sie dieses Element löschen, werden auch alle Kind-Elemente gelöscht!');
    }

    var deleteElement = function(elem, onSuccess) {
        console.log("Removing element " + elem.name + " with ID " + elem.id);
        httpGetFactory('../spacialist_api/context/delete/' + elem.id, function(callback) { onSuccess(); });
    };

    $scope.existsLiterature = function(fid, aid) {
        var promise = httpGetPromise.getData('../spacialist_api/sources/get/' + aid + '/' + fid);
        promise.then(function(sources) {
            console.log(aid);
            console.log(sources);
            console.log((sources.length > 0));
            return sources.length > 0;
        });
        /*httpGetFactory('../spacialist_api/sources/get/' + aid + '/' + fid, function(sources) {
            return sources.length > 0;
        });*/
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

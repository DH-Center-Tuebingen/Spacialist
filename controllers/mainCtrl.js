spacialistApp.controller('mainCtrl', ['$scope', 'mainService', 'mapService', '$state', '$translate', '$timeout', function($scope, mainService, mapService, $state, $translate, $timeout) {
    $scope.moduleExists = mainService.moduleExists;
    $scope.filterTree = mainService.filterTree;

    var localContexts = this.contexts;
    var localConcepts = this.concepts;
    var localLayers = this.layer;
    var localMap = this.map;
    var mapObject;
    var localGeodata = this.geodata;
    var localContextTypes = this.contextTypes;

    this.onStore = function(context, data) {
        var c = this.contexts.data[context.id];
        mainService.storeElement(c, data);
    };

    mapService.setupLayers(localLayers, localMap, localContexts, localConcepts);
    mapService.initMapObject().then(function(obj) {
        mapObject = obj;
        // wait a random amount of time, so mapObject.eachLayer has all layers
        $timeout(function() {
            mapObject.eachLayer(function(l) {
                if(l.options.layer_id) {
                    localMap.mapLayers[l.options.layer_id] = l;
                }
            });
            mapService.initGeodata(localGeodata, localContexts, localMap);
        }, 100);
    });

    $scope.layerTwo = {
        activeTab: 'map',
        imageTab: {}
    };

    $scope.setActiveTab = function(tabId) {
        $scope.layerTwo.activeTab = tabId;
    };

    $scope.treeCallbacks = {
        toggle: function(collapsed, sourceNodeScope) {
            mainService.treeCallbacks.toggle(collapsed, sourceNodeScope, localContexts);
        },
        dropped: function(event) {
            mainService.treeCallbacks.dropped(event, localContexts);
        }
    };

    $scope.treeOptions = {
        getColorForId: mainService.getColorForId,
        'new-context-link': 'root.spacialist.add({type: "context"})'
    };

    $scope.newElementContextMenu = [
        [
            function($itemScope, $event, modelValue, text, $li) {
                return $translate.instant('context-menu.options-of', { object: localContexts.data[$itemScope.$parent.id].name });
            },
            function($itemScope, $event, modelValue, text, $li) {
            },
            function() { return false; }
        ],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">add_circle_outline</i> ' + $translate.instant('context-menu.new-artifact');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.add', {
                    type: 'find',
                    id: $itemScope.$parent.id
                });
        }, function($itemScope) {
            return this.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">add_circle_outline</i> ' + $translate.instant('context-menu.new-context');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.add', {
                    type: 'context',
                    id: $itemScope.$parent.id
                });
        }, function($itemScope) {
            return this.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">content_copy</i> ' + $translate.instant('context-menu.duplicate-element');
            },
                function($itemScope, $event, modelValue, text, $li) {
            mainService.duplicateElement($itemScope.$parent.id);
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-red context-menu-icon">delete</i> ' + $translate.instant('context-menu.delete');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.delete', {id: $itemScope.$parent.id});
                // mainService.deleteElement(localContexts, $itemScope.$parent.id);
            }
        ]
    ];
}]);

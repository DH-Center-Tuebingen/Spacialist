spacialistApp.controller('mainCtrl', ['$scope', 'mainService', 'mapService', '$translate', '$timeout', function($scope, mainService, mapService, $translate, $timeout) {
    $scope.moduleExists = mainService.moduleExists;

    var localContexts = this.contexts;
    var localLayers = this.layer;
    var localMap = this.map;
    var mapObject;
    var localGeodata = this.geodata;

    mapService.setupLayers(localLayers, localMap, localContexts);
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
            mainService.treeCallbacks.dropped(event, contexts);
        }
    };

    $scope.setCurrentElement = function(target, elem) {

    }

    $scope.treeOptions = {
        getColorForId: mainService.getColorForId,
        createNewContext: mainService.createNewContext
    };

    $scope.newElementContextMenu = [
        [
            function($itemScope, $event, modelValue, text, $li) {
                return $translate.instant('context-menu.options-of', { object: this.contexts.data[$itemScope.$parent.id].name });
            },
            function($itemScope, $event, modelValue, text, $li) {
            },
            function() { return false; }
        ],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green">add_circle_outline</i> ' + $translate.instant('context-menu.new-artifact');
            },
            function($itemScope, $event, modelValue, text, $li) {
            createModalHelper(this.contexts.data[$itemScope.$parent.id], 'find', false);
        }, function($itemScope) {
            return this.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green">add_circle_outline</i> ' + $translate.instant('context-menu.new-context');
            },
            function($itemScope, $event, modelValue, text, $li) {
            createModalHelper(this.contexts.data[$itemScope.$parent.id], 'context', false);
        }, function($itemScope) {
            return this.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green">content_copy</i> ' + $translate.instant('context-menu.duplicate-element');
            },
                function($itemScope, $event, modelValue, text, $li) {
            mainService.duplicateElement($itemScope.$parent.id);
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-red">delete</i> ' + $translate.instant('context-menu.delete');
            },
            function($itemScope, $event, modelValue, text, $li) {
                mainService.deleteElement(this.contexts.data[$itemScope.$parent.id]);
            }
        ]
    ];
}]);

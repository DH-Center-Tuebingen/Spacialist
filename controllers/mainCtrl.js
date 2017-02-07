spacialistApp.controller('mainCtrl', ['$rootScope', '$scope', 'userService', 'analysisService', 'mainService', 'literatureService', 'modalFactory', function($rootScope, $scope, userService, analysisService, mainService, literatureService, modalFactory) {
    $scope.literature = literatureService.literature;

    $scope.currentUser = userService.currentUser;
    $scope.can = userService.can;

    $scope.currentElement = mainService.currentElement;
    $scope.contexts = mainService.contexts;
    $scope.contextReferences = mainService.contextReferences;
    $scope.artifacts = mainService.artifacts;
    $scope.artifactReferences = mainService.artifactReferences;
    $scope.dropdownOptions = mainService.dropdownOptions;
    $scope.getColorForId = mainService.getColorForId;
    $scope.contextList = mainService.contextList;
    $scope.moduleExists = mainService.moduleExists;
    $scope.setCurrentElement = mainService.setCurrentElement;
    $scope.unsetCurrentElement = mainService.unsetCurrentElement;
    var createModalHelper = mainService.createModalHelper;

    $scope.storedQueries = analysisService.storedQueries;

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
            createModalHelper($itemScope, 'find', false);
        }, function($itemScope) {
            return $itemScope.parent.typeid === 0;
        }],
        ['<span class="fa fa-fw fa-plus-circle fa-light fa-green"></span> Neuer Kontext', function($itemScope, $event, modelValue, text, $li) {
            createModalHelper($itemScope, 'context', false);
        }, function($itemScope) {
            return $itemScope.parent.typeid === 0;
        }],
        null,
        ['<span class="fa fa-fw fa-clone fa-light fa-green"></span> Element duplizieren', function($itemScope, $event, modelValue, text, $li) {
            mainService.duplicateElement($itemScope);
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
        activeTab: 'map',
        imageTab: {}
    };

    $scope.setActiveTab = function(tabId) {
        $scope.layerTwo.activeTab = tabId;
    };

    $scope.createNewContext = function(data) {
        mainService.createNewContext(data);
    };

    $rootScope.$on('unsetCurrentElement', function(event, args) {
        $scope.unsetCurrentElement();
    });

    $rootScope.$on('setCurrentElement', function(event, args) {
        $scope.setCurrentElement(args.source, args.target);
    });

    $scope.storeElement = function(elem, data) {
        mainService.storeElement(elem, data);
    };

    $scope.deleteElement = function(elem) {
        mainService.deleteElement(elem);
    };

    /**
     * Opens a modal window which allows the user to add/delete sources from a literature list for a particular attribute.
     * One has to pass the field name `fieldname` and the attribute id `fieldid` as parameters.
     */
    $scope.openSourceModal = function(fieldname, fieldid, currentVal) {
        mainService.openSourceModal(fieldname, fieldid, currentVal);
    };
}]);

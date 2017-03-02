spacialistApp.controller('mainCtrl', ['$rootScope', '$scope', 'userService', 'analysisService', 'mainService', 'literatureService', 'modalFactory', '$translate', function($rootScope, $scope, userService, analysisService, mainService, literatureService, modalFactory, $translate) {
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
    $scope.analysisEntries = analysisService.entries;
    $scope.activeAnalysis = analysisService.activeAnalysis;
    var createModalHelper = mainService.createModalHelper;

    $scope.storedQueries = analysisService.storedQueries;

    $scope.newElementContextMenu = [
        [
            function($itemScope, $event, modelValue, text, $li) {
                return $translate('context-menu.options-of', { object: $itemScope.parent.name });
            },
            function($itemScope, $event, modelValue, text, $li) {
            },
            function() { return false; }
        ],
        null,
        [
            function() {
                return '<span class="fa fa-fw fa-plus-circle fa-light fa-green"></span> ' + $translate.instant('context-menu.new-artifact');
            },
            function($itemScope, $event, modelValue, text, $li) {
            createModalHelper($itemScope, 'find', false);
        }, function($itemScope) {
            return $itemScope.parent.typeid === 0;
        }],
        [
            function() {
                return '<span class="fa fa-fw fa-plus-circle fa-light fa-green"></span> ' + $translate.instant('context-menu.new-context');
            },
            function($itemScope, $event, modelValue, text, $li) {
            createModalHelper($itemScope, 'context', false);
        }, function($itemScope) {
            return $itemScope.parent.typeid === 0;
        }],
        null,
        [
            function() {
                return '<span class="fa fa-fw fa-clone fa-light fa-green"></span> ' + $translate.instant('context-menu.duplicate-element');
            },
                function($itemScope, $event, modelValue, text, $li) {
            mainService.duplicateElement($itemScope);
        }],
        null,
        [
            function() {
                return '<span class="fa fa-fw fa-trash-o fa-light fa-red"></span> ' + $translate.instant('context-menu.delete')
            },
                function($itemScope, $event, modelValue, text, $li) {
            modalFactory.deleteModal($itemScope.parent.name, function() {
                deleteElement($itemScope.parent, function() {
                    $itemScope.remove();
                });
            }, 'delete-confirm.warning');
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

    $scope.hasSources = function(elem) {
        return !mainService.isEmpty(elem.sources);
    };

    $scope.deleteSourceEntry = function(index, key) {
        mainService.deleteSourceEntry(index, key);
    };

    $scope.addListEntry = function(aid, oid, text, arr) {
        var index = aid + '_' + (oid || '');
        var tmpArr = $scope.$eval(arr);
        var inp = $scope.$eval(text);
        if(typeof tmpArr[index] == 'undefined') tmpArr[index] = [];
        tmpArr[index].push({
            'name': inp[index]
        });
        inp[index] = '';
    };

    $scope.editListEntry = function(ctid, aid, $index, val, tableIndex) {
        $scope.cancelEditListEntry();
        var name = ctid + "_" + aid;
        $scope.currentEditName = name;
        $scope.currentEditIndex = $index;
        if (typeof tableIndex !== 'undefined') {
            $scope.currentEditCol = tableIndex;
            $scope.editEntry[name][$index][tableIndex] = true;
        } else {
            $scope.editEntry[name][$index] = true;
        }
        $scope.initialListVal = val;
    };

    $scope.cancelEditListEntry = function() {
        if (typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if (typeof $scope.currentEditCol !== 'undefined') {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex][$scope.currentEditCol] = false;
                $scope.markerValues[$scope.currentEditName].selectedEpochs[$scope.currentEditIndex][$scope.currentEditCol] = $scope.initialListVal;
            } else {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex] = false;
                $scope.markerValues[$scope.currentEditName][$scope.currentEditIndex] = $scope.initialListVal;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    };

    $scope.storeEditListEntry = function() {
        if (typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if (typeof $scope.currentEditCol !== 'undefined') {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex][$scope.currentEditCol] = false;
            } else {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex] = false;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    };

    $scope.removeListItem = function(aid, oid, arr, $index) {
        var index = aid + '_' + (oid || '');
        var tmpArr = $scope.$eval(arr);
        tmpArr[index].splice($index, 1);
        //var name = aid + "_" + oid;
        //$scope.markerValues[name].splice($index, 1);
    };

    $scope.toggleList = function(ctid, aid) {
        var index = ctid + "_" + aid;
        $scope.hideLists[index] = !$scope.hideLists[index];
    };

    /**
     * Opens a modal window which allows the user to add/delete sources from a literature list for a particular attribute.
     * One has to pass the field name `fieldname` and the attribute id `fieldid` as parameters.
     */
    $scope.openSourceModal = function(fieldname, fieldid, currentVal, currentDesc) {
        mainService.openSourceModal(fieldname, fieldid, currentVal, currentDesc);
    };

    $scope.openGeographyPlacer = function() {
        mainService.openGeographyModal($scope);
    };
}]);

spacialistApp.controller('optionCtrl', ['$scope', '$timeout', 'scopeService', function($scope, $timeout, scopeService) {
    if(typeof $scope.allContexts == 'undefined') $scope.allContexts = scopeService.allContexts;
    $scope.deleteLiteratureEntry = scopeService.deleteLiteratureEntry;

    /*
     * Opens an editable input field at the desired position in the list
     */
    $scope.editListEntry = function(cid, aid, oid, $index, val, tableIndex) {
        $scope.cancelEditListEntry();
        if(typeof oid == 'undefined') oid = "";
        var name = cid + "_" + aid + "_" + oid;
        $scope.currentEditName = name;
        $scope.currentEditIndex = $index;
        if(typeof tableIndex !== 'undefined') {
            $scope.currentEditCol = tableIndex;
            $scope.output[name].editEntry[$index][tableIndex] = true;
        } else {
            $scope.output[name].editEntry[$index] = true;
        }
        $scope.initialListVal = val;
    }

    /*
     * Removes the editable input field and resets all values to their initial value
     */
    $scope.cancelEditListEntry = function() {
        if(typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if(typeof $scope.currentEditCol !== 'undefined') {
                $scope.output[$scope.currentEditName].editEntry[$scope.currentEditIndex][$scope.currentEditCol] = false;
                $scope.output[$scope.currentEditName].selectedEpochs[$scope.currentEditIndex][$scope.currentEditCol] = $scope.initialListVal;
            } else {
                $scope.output[$scope.currentEditName].editEntry[$scope.currentEditIndex] = false;
                $scope.output[$scope.currentEditName][$scope.currentEditIndex] = $scope.initialListVal;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    }

    /*
     * Stores the edited value in the current element
     * TODO store in DB as well
     */
    $scope.storeEditListEntry = function() {
        if(typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if(typeof $scope.currentEditCol !== 'undefined') {
                $scope.output[$scope.currentEditName].editEntry[$scope.currentEditIndex][$scope.currentEditCol] = false;
            } else {
                $scope.output[$scope.currentEditName].editEntry[$scope.currentEditIndex] = false;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    }
}]);

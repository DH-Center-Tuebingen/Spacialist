spacialistApp.controller('dataEditorCtrl', ['$scope', 'dataEditorService', function($scope, dataEditorService) {
    var localAttributes = this.attributes;
    var localTypes = this.attributetypes;

    $scope.addNewAttribute = function() {
        dataEditorService.addNewAttributeWindow(localTypes, localAttributes);
    };

    $scope.onDeleteAttribute = function(attr) {
        dataEditorService.deleteAttribute(attr, localAttributes);
    };
}]);

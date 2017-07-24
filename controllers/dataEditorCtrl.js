spacialistApp.controller('dataEditorCtrl', ['$scope', 'dataEditorService', function($scope, dataEditorService) {
    var localAttributes = this.attributes;
    var localTypes = this.attributetypes;
    var localGeomTypes = this.geometryTypes;
    var localContextTypes = this.contextTypes;
    var localContextType = this.contextType;
    var localFields = this.fields;
    var localConcepts = this.concepts;

    $scope.addNewAttribute = function() {
        dataEditorService.addNewAttributeWindow(localTypes, localAttributes);
    };

    $scope.onDeleteAttribute = function(attr) {
        dataEditorService.deleteAttribute(attr, localAttributes);
    };

    $scope.addNewContextType = function() {
        dataEditorService.addNewContextTypeWindow(localGeomTypes, localContextTypes);
    };

    $scope.editContextType = function(e) {
        dataEditorService.editContextType(e);
    };

    $scope.deleteContextType = function(e) {
        dataEditorService.deleteContextType(e, localContextTypes, localConcepts[e.thesaurus_url].label);
    };

    $scope.onRemoveAttrFromCt = function(attr) {
        dataEditorService.removeAttributeFromContextType(localContextType, attr, localFields);
    };

    $scope.onOrderAttrOfCt = {
        up: function(attr) {
            dataEditorService.moveAttributeOfContextTypeUp(attr, localFields);
        },
        down: function(attr) {
            dataEditorService.moveAttributeOfContextTypeDown(attr, localFields);
        }
    };

    $scope.addAttributeToContextType = function() {
        dataEditorService.addAttributeToContextTypeWindow(localContextType, localFields, localAttributes, localConcepts);
    };
}]);

spacialistApp.service('editorService', ['httpGetFactory', function(httpGetFactory) {
    var editor = {};

    editor.attributeTypes = [];
    editor.existingAttributes = [];
    editor.existingContextTypes = [];
    editor.contextAttributes = {};

    init();

    function init() {
        httpGetFactory('api/context/get/attributes', function(response) {
            angular.forEach(response.attributes, function(a) {
                var label = a.label;
                if(a.root_label)  label += ' (' + a.root_label + ')';
                editor.existingAttributes.push({
                    aid: a.id,
                    datatype: a.datatype,
                    val: label
                });
            });
        });
        httpGetFactory('api/context/get/attributes/types', function(response) {
            angular.forEach(response.types, function(t) {
                editor.attributeTypes.push({
                    datatype: t.datatype,
                    description: t.description
                });
            });
        });
    }

    return editor;
}]);

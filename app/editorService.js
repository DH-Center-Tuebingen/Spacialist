spacialistApp.service('editorService', ['httpGetFactory', 'httpPostFactory', 'httpPostPromise', 'modalFactory', 'mainService', function(httpGetFactory, httpPostFactory, httpPostPromise, modalFactory, mainService) {
    var editor = {};

    editor.attributeTypes = [];
    editor.existingAttributes = [];
    editor.contextAttributes = {};
    editor.existingContextTypes = mainService.contexts;
    editor.existingArtifactTypes =  mainService.artifacts;

    editor.addNewContextTypeWindow = function() {
        modalFactory.newContextTypeModal(searchForLabel, addNewContextType);
    };

    function addNewContextType(label, type) {
        if(!label || !type)  return;
        var formData = new FormData();
        formData.append('concept_url', label.concept_url);
        formData.append('type', type.id);
        httpPostFactory('api/editor/contexttype/add', formData, function(response) {
            if(response.error) {
                return;
            }
            var c = response.contexttype;
            var newType = {
                title: c.label,
                index: c.thesaurus_url,
                type: parseInt(c.type),
                ctid: c.id
            };
            if(newType.type === 0) {
                editor.existingContextTypes.push(newType);
            } else if(response.contexttype.type == 1) {
                editor.existingArtifactTypes.push(newType);
            }
        });
    }

    function searchForLabel(searchString) {
        var formData = new FormData();
        formData.append('val', searchString);
        return httpPostPromise.getData('api/editor/search', formData)
        .then(function(response) {
            return response;
        });
    }

    init();

    function init() {
        httpGetFactory('api/context/get/attributes', function(response) {
            angular.forEach(response.attributes, function(a) {
                var entry = {
                    aid: a.id,
                    datatype: a.datatype,
                    val: a.label
                };
                if(a.root_label) entry.root_label = a.root_label;
                editor.existingAttributes.push(entry);
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

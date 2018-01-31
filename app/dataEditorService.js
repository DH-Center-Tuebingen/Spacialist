spacialistApp.service('dataEditorService', ['httpGetFactory', 'httpGetPromise', 'httpPostFactory', 'httpPostPromise', 'httpPatchFactory', 'httpDeleteFactory', 'modalFactory', 'mainService', 'mapService', 'langService', '$translate', function(httpGetFactory, httpGetPromise, httpPostFactory, httpPostPromise, httpPatchFactory, httpDeleteFactory, modalFactory, mainService, mapService, langService, $translate) {
    var editor = {};

    editor.ct = {
        selected: {},
        attributes: []
    };
    editor.attributeTypes = [];
    editor.existingAttributes = [];
    editor.contextAttributes = {};
    editor.availableGeometryTypes = [];
    editor.existingContextTypes = mainService.contextTypes;
    editor.existingArtifactTypes =  mainService.artifacts;
    editor.contextAttributes = mainService.contextReferences;
    editor.contexts = mainService.contexts;
    editor.dropdownOptions = mainService.dropdownOptions;

    editor.setSelectedContext = function(c) {
        editor.ct.selected = c;
        editor.ct.attributes = getCtAttributes(c);
    };

    editor.addNewContextTypeWindow = function(geometryTypes, contextTypes) {
        modalFactory.newContextTypeModal(searchForLabel, addNewContextType, geometryTypes, contextTypes);
    };

    editor.addNewAttributeWindow = function(datatypes, attributes) {
        modalFactory.addNewAttributeModal(searchForLabel, addNewAttribute, datatypes, attributes);
    };

    editor.addAttributeToContextTypeWindow = function(contextType, ctAttributes, attributes, concepts) {
        var attrs =  attributes.filter(function(a) {
            for(var i=0; i<ctAttributes.length; i++) {
                if(ctAttributes[i].id == a.id) return false;
            }
            return true;
        });
        modalFactory.addAttributeToContextTypeModal(concepts, contextType, ctAttributes, attrs, addAttributeToContextType);
    };

    editor.removeAttributeFromContextType = function(context, attr, attributes) {
        if(attr.context_type_id != context.id) return; // context_type of attribute doesn't match context id
        httpDeleteFactory('api/editor/context_type/' + context.id + '/attribute/' + attr.id, function(response) {
            if(!response.error) {
                var i = attr.position - 1; // positions start at 1, indices at 0
                if(i > -1) {
                    attributes.splice(i, 1);
                    for(var j=i; j<attributes.length; j++) {
                        attributes[j].position--;
                    }
                }
            }
        });
    };

    editor.moveAttributeOfContextTypeUp = function(attr, attributes) {
        if(attr.position == 1) return; //topmost element can not be moved up
        var ctid = attr.context_type_id;
        var aid = attr.id;
        var i = attr.position - 1; // positions start at 1, indices at 0
        httpPatchFactory('api/editor/context_type/' + ctid + '/attribute/' + aid + '/move/up', new FormData(), function(response) {
            if(!response.error) {
                attributes[i].position--;
                attributes[i-1].position++;
                swap(attributes, i, i-1);
            }
        });
    };
    editor.moveAttributeOfContextTypeDown = function(attr, attributes) {
        if(attr.position == attributes.length) return; //bottommost element can not be moved down
        var ctid = attr.context_type_id;
        var aid = attr.id;
        var i = attr.position - 1; // positions start at 1, indices at 0
        httpPatchFactory('api/editor/context_type/' + ctid + '/attribute/' + aid + '/move/down', new FormData(), function(response) {
            if(!response.error) {
                attributes[i].position++;
                attributes[i+1].position--;
                swap(attributes, i, i+1);
            }
        });
    };

    editor.deleteAttribute = function(attr, attributes) {
        httpDeleteFactory('api/editor/attribute/' + attr.id, function(response) {
            if(!response.error) {
                var i = attributes.indexOf(attr);
                if(i > -1) {
                    attributes.splice(i, 1);
                }
            }
        });
    };

    editor.editContextType = function(e) {
        modalFactory.editContextTypeModal(editContextType, searchForLabel, e, e.title);
    };

    function editContextType(e, newType) {
        var formData = new FormData();
        formData.append('new_url', newType.concept_url);
        httpPatchFactory('api/editor/context_type/' + e.id, formData, function(response) {
            if(response.error) {
                // TODO
                return;
            }
            e.thesaurus_url = newType.concept_url;
        });
    }

    editor.deleteContextType = function(e, contextTypes, contextName) {
        httpGetFactory('api/editor/occurrence_count/' + e.id, function(response) {
            return $translate('context-type.delete-warning', {
                element: contextName,
                cnt: response.count
            }).then(function(t) {
                var onConfirm = function() {
                    return deleteContextType(e, contextTypes);
                };
                modalFactory.deleteModal(contextName, onConfirm, t);
            });
        });
    };

    function deleteContextType(e, contextTypes) {
        httpDeleteFactory('api/editor/contexttype/' + e.id, function(response) {
            if(!response.error) {
                var id = contextTypes.indexOf(e);
                if(id > -1) contextTypes.splice(id, 1);
            }
        });
    }

    function addNewContextType(label, geomtype, root, contexttypes) {
        if(!label || !type)  return;
        var formData = new FormData();
        formData.append('concept_url', label.concept_url);
        formData.append('is_root', root);
        formData.append('geomtype', geomtype);
        httpPostFactory('api/editor/context_type', formData, function(response) {
            if(response.error) {
                return;
            }
            contexttypes.push(response.contexttype);
        });
    }

    function addNewAttribute(label, datatype, parent, attributes, columns) {
        var formData = new FormData();
        formData.append('label_id', label.id);
        formData.append('datatype', datatype.datatype);
        if(datatype.datatype == 'table') formData.append('columns', angular.toJson(columns));
        if(parent) formData.append('parent_id', parent.id);
        httpPostFactory('api/editor/attribute', formData, function(response) {
            if(!response.error) {
                attributes.push(response.attribute);
            }
        });
    }

    function addAttributeToContextType(attr, contextType, contextAttributes) {
        var formData = new FormData();
        formData.append('attribute_id', attr.id);

        httpPostFactory('api/editor/context_type/' + contextType.id + '/attribute', formData, function(response) {
            if(!response.error) {
                contextAttributes.push(response.attribute);
            }
        });
    }

    function searchForLabel(searchString) {
        //TODO might want to specify language of the label aswell, default is 'de'
        // api call for that case is 'api/editor/search/label=' + searchString + "/" + lang
        var lang = langService.getCurrentLanguage();
        return httpGetPromise.getData('api/editor/search/label=' + searchString + '/' + lang)
        .then(function(response) {
            return response;
        });
    }

    editor.getAttributes = function() {
        return httpGetPromise.getData('api/context/attribute').then(function(response) {
            return response;
        });
    };

    editor.getContextTypes = function() {
        return httpGetPromise.getData('api/context/context_type').then(function(response) {
            console.log(response);
            return response;
        });
    };

    editor.getAttributeTypes = function() {
        return httpGetPromise.getData('api/context/attributetypes').then(function(response) {
            return response;
        });
    };

    editor.getGeometryTypes = function() {
        return httpGetPromise.getData('api/overlay/geometry_types').then(function(response) {
            return response;
        });
    };

    return editor;
}]);

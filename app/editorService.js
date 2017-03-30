spacialistApp.service('editorService', ['httpGetFactory', 'httpPostFactory', 'httpPostPromise', 'modalFactory', 'mainService', '$translate', function(httpGetFactory, httpPostFactory, httpPostPromise, modalFactory, mainService, $translate) {
    var editor = {};

    editor.ct = {
        selected: {},
        attributes: []
    };
    editor.attributeTypes = [];
    editor.existingAttributes = [];
    editor.contextAttributes = {};
    editor.existingContextTypes = mainService.contexts;
    editor.existingArtifactTypes =  mainService.artifacts;
    editor.contextAttributes = mainService.contextReferences;
    editor.contextList = mainService.contextList;
    editor.dropdownOptions = mainService.dropdownOptions;

    editor.setSelectedContext = function(c) {
        editor.ct.selected = c;
        editor.ct.attributes = getCtAttributes(c);
    };

    editor.addNewContextTypeWindow = function() {
        modalFactory.newContextTypeModal(searchForLabel, addNewContextType);
    };

    editor.addNewAttributeWindow = function() {
        modalFactory.addNewAttributeModal(searchForLabel, addNewAttribute, editor.attributeTypes);
    };

    editor.addAttributeToContextTypeWindow = function(ct) {
        var setAttrs = getCtAttributes(ct);
        var attrs =  editor.existingAttributes.filter(function(i) {
            for(var j=0; j<setAttrs.length; j++) {
                if(setAttrs[j].aid == i.aid) return false;
            }
            return true;
        });
        modalFactory.addAttributeToContextTypeModal(ct, attrs, addAttributeToContextType);
    };

    editor.removeAttributeFromContextType = function(attr, context) {
        var formData = new FormData();
        formData.append('aid', attr.aid);
        formData.append('ctid', attr.context_type_id);
        httpPostFactory('api/editor/contexttype/attribute/remove', formData, function(response) {
            if(!response.error) {
                var i = editor.ct.attributes.indexOf(attr);
                if(i > -1) {
                    editor.ct.attributes.splice(i, 1);
                    for(var j=i; j<editor.ct.attributes.length; j++) {
                        editor.ct.attributes[j].position--;
                    }
                }
            }
        });
    };

    editor.moveAttributeOfContextTypeUp = function(attr) {
        if(attr.position == 1) return; //topmost element can not be moved up
        var i = editor.ct.attributes.indexOf(attr);
        if(i == -1) return; //element is not part of attributes
        if(i+1 != attr.position) return; // array position does not match stored position
        var formData = new FormData();
        console.log(attr);
        formData.append('ctid', attr.context_type_id);
        formData.append('aid', attr.aid);
        httpPostFactory('api/editor/contexttype/attribute/move/up', formData, function(response) {
            if(!response.error) {
                editor.ct.attributes[i].position--;
                editor.ct.attributes[i-1].position++;
                editor.ct.attributes.swap(i, i-1);
            }
        });
    };
    editor.moveAttributeOfContextTypeDown = function(attr) {
        if(attr.position == editor.ct.attributes.length) return; //bottommost element can not be moved down
        var i = editor.ct.attributes.indexOf(attr);
        if(i == -1) return; //element is not part of attributes
        if(i+1 != attr.position) return; // array position does not match stored position
        var formData = new FormData();
        formData.append('ctid', attr.context_type_id);
        formData.append('aid', attr.aid);
        httpPostFactory('api/editor/contexttype/attribute/move/down', formData, function(response) {
            if(!response.error) {
                editor.ct.attributes[i].position++;
                editor.ct.attributes[i+1].position--;
                editor.ct.attributes.swap(i, i+1);
            }
        });
    };

    editor.deleteAttribute = function(attr) {
        httpGetFactory('api/editor/attribute/delete/' + attr.aid, function(response) {
            if(!response.error) {
                var i = editor.existingAttributes.indexOf(attr);
                if(i > -1) {
                    editor.existingAttributes.splice(i, 1);
                    angular.forEach(editor.existingContextTypes, function(t) {
                        var attrs = getCtAttributes(t);
                        var found = false;
                        angular.forEach(attrs, function(a, k) {
                            if(!found) {
                                if(a.aid == attr.aid) {
                                    attrs.splice(k, 1);
                                    found = true;
                                }
                            }
                        });
                    });
                }
            }
        });
    };

    editor.editContextType = function(e) {
        modalFactory.editContextTypeModal(editContextType, searchForLabel, e, e.title);
    };

    function editContextType(e, newType) {
        var formData = new FormData();
        formData.append('ctid', e.context_type_id);
        formData.append('new_url', newType.concept_url);
        httpPostFactory('api/editor/contexttype/edit', formData, function(response) {
            if(!response.error) {
                var refs;
                if(e.type === 0) {
                    refs = mainService.contextReferences;
                }
                else if(e.type == 1) {
                    refs = mainService.artifactReferences;
                }
                refs[newType.concept_url] = refs[e.index];
                delete refs[e.index];
                var oldUrl = e.index;
                e.title = newType.label;
                e.index = newType.concept_url;
                updateContextList(oldUrl, newType);
            }
        });
    }

    editor.deleteElementType = function(e) {
        httpGetFactory('api/editor/occurrences/' + e.context_type_id, function(response) {
            $translate('context-type.delete-warning', {
                element: e.title,
                cnt: response.count
            }).then(function(t) {
                var onConfirm = function() {
                    return deleteElementType(e);
                };
                modalFactory.deleteModal(e.title, onConfirm, t);
            });
        });
    };

    function deleteElementType(e) {
        httpGetFactory('api/editor/contexttype/delete/' + e.context_type_id, function(response) {
            if(!response.error) {
                var id;
                if(e.type === 0) {
                    id = editor.existingContextTypes.indexOf(e);
                    editor.existingContextTypes.splice(id, 1);
                } else if(e.type == 1) {
                    id = editor.existingArtifactTypes.indexOf(e);
                    editor.existingArtifactTypes.splice(id, 1);
                }
                updateContextList(e.index, undefined);
            }
        });
    }

    function updateContextList(oldUrl, newType) {
        if(!oldUrl || oldUrl.length === 0) return;
        var isDelete = !newType;
        angular.forEach(editor.contextList, function(c, i) {
            if(c.typename == oldUrl) {
                if(isDelete) {
                    editor.contextList.splice(i, 1);
                } else {
                    c.typename = newType.concept_url;
                    c.typelabel = newType.label;
                }
            }
        });
    }

    function getCtAttributes(ct) {
        if(ct.type === 0) {
            return mainService.contextReferences[ct.index];
        }
        else if(ct.type == 1) {
            return mainService.artifactReferences[ct.index];
        }
        return [];
    }

    function initReferences(ct) {
        if(ct.type === 0) {
            if(!mainService.contextReferences[ct.index]) {
                mainService.contextReferences[ct.index] = [];
            }
        }
        else if(ct.type == 1) {
            if(!mainService.artifactReferences[ct.index]) {
                mainService.artifactReferences[ct.index] = [];
            }
        }
    }

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
                context_type_id: c.id
            };
            initReferences(newType);
            if(newType.type === 0) {
                editor.existingContextTypes.push(newType);
            } else if(response.contexttype.type == 1) {
                editor.existingArtifactTypes.push(newType);
            }
        });
    }

    function addNewAttribute(label, datatype, parent) {
        var formData = new FormData();
        formData.append('label_id', label.id);
        formData.append('datatype', datatype.datatype);
        if(parent) formData.append('parent_id', parent.id);
        httpPostFactory('api/editor/attribute/add', formData, function(response) {
            if(!response.error) {
                var a = response.attribute;
                var addedAttr = {
                    aid: a.id,
                    datatype: a.datatype,
                    val: a.label
                };
                if(a.root_label) addedAttr.root_label = a.root_label;
                editor.existingAttributes.push(addedAttr);
            }
        });
    }

    function addAttributeToContextType(attr, ct) {
        var formData = new FormData();
        formData.append('ctid', ct.context_type_id);
        formData.append('aid', attr.aid);

        httpPostFactory('api/editor/contexttype/attribute/add', formData, function(response) {
            if(!response.error) {
                var a = response.attribute;
                var addedAttribute = {
                    aid: parseInt(a.attribute_id),
                    context_type_id: parseInt(a.context_type_id),
                    val: a.val,
                    datatype: a.datatype,
                    position: a.position
                };
                getCtAttributes(ct).push(addedAttribute);
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

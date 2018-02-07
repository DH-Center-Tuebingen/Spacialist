spacialistApp.component('spacialist', {
    bindings: {
        tab: '@',
        userConfig: '<',
        editMode: '<',
        contexts: '<',
        globalContext: '<',
        globalGeodata: '<',
        user: '<',
        concepts:'<',
        menus: '<',
        map: '<',
        layer: '<',
        geodata: '<',
        contextTypes: '<',
        geometryTypes: '<',
        subContextTypes: '<',
        allowedSubContextTypes: '<',
        files: '<',
        availableTags: '<',
        availableSearchTerms: '<'
    },
    templateUrl: 'view.html',
    controller: 'mainCtrl'
});

spacialistApp.component('spacialistcontext', {
    bindings: {
        onStore: '&',
        onSetContextWrapper: '&',
        onSetGeodataWrapper: '&',
    },
    templateUrl: 'templates/context-wrapper.html',
    controller: function() {
        var vm = this;

        vm.onStoreWrapper = function(context, data) {
            vm.onStore({context: context, data: data});
        }

        vm.onSetGeodata = function(gid, geodata) {
            vm.onSetGeodataWrapper({gid: gid, geodata: geodata});
        };
        vm.onSetContext = function(id, data) {
            vm.onSetContextWrapper({id: id, data: data});
        };
    }
});

spacialistApp.component('spacialistdata', {
    bindings: {
        tab: '@',
        context: '<',
        editContext: '<',
        data: '<',
        fields: '<',
        sources: '<',
        menus: '<',
        user: '<',
        concepts: '<',
        linkedFiles: '<',
        layer: '<',
        onStore: '&',
        onSourceAdd: '&',
        map: '<',
        globalGeodata: '<',
        mapContentLoaded: '<',
        onSetContext: '&',
        onSetGeodata: '&'
    },
    templateUrl: 'templates/context-data.html',
    controller: 'contextCtrl'
});

spacialistApp.component('sourcemodal', {
        templateUrl: "modals/sources.html",
        bindings: {
            attribute: '<',
            certainty: '<',
            attributesources: '<',
            context: '<',
            literature: '<',
            sources: '<',
            onAdd: '&',
            onClose: '&',
            onDismiss: '&'
        },
        controller: ['$scope', 'snackbarService', 'httpPutFactory', 'httpPostFactory', 'httpDeleteFactory', '$translate', function($scope, snackbarService, httpPutFactory, httpPostFactory, httpDeleteFactory, $translate) {
            var vm = this;

            vm.newEntry = {
                source: '',
                desc: ''
            };

            vm.updateCertainty = function() {
                var formData = new FormData();
                formData.append('possibility', vm.certainty.certainty);
                if(vm.certainty.description) formData.append('possibility_description', vm.certainty.description);
                httpPutFactory('api/context/attribute_value/'+vm.context.id+'/'+vm.attribute.id, formData, function(callback) {
                    var content = $translate.instant('snackbar.data-stored.success');
                    snackbarService.addAutocloseSnack(content, 'success');
                });
            };

            vm.cancel = function() {
                vm.onDismiss();
            };
            vm.addSource = function(entry) {
                // vm.onAdd({entry: entry});
                var formData = new FormData();
                formData.append('cid', vm.context.id);
                formData.append('aid', vm.attribute.id);
                formData.append('lid', entry.source.id);
                formData.append('desc', entry.desc);
                httpPostFactory('api/source', formData, function(response) {
                    vm.attributesources.push(response.source);
                    entry.source = undefined;
                    entry.desc = '';
                });
            };
            vm.deleteSourceEntry = function(id) {
                var entry = vm.attributesources.find(function(s) {
                    return s.id == id;
                });
                httpDeleteFactory('api/source/' + entry.id, function() {
                    var index = vm.attributesources.indexOf(entry);
                    if(index > -1) {
                        vm.attributesources.splice(index, 1);
                    }
                });
            };
            vm.setCertainty = function(event, certainty) {
                var max = event.currentTarget.scrollWidth;
                var click = event.originalEvent.layerX;
                var curr = angular.copy(certainty.certainty);
                var newVal = parseInt(click/max*100);
                if(Math.abs(newVal-curr) < 10) {
                    if(newVal > curr) newVal = parseInt((newVal+10)/10)*10;
                    else newVal = parseInt(newVal/10)*10;
                } else {
                    newVal = parseInt((newVal+5)/10)*10;
                }
                event.currentTarget.children[0].style.width = newVal+"%";
                certainty.certainty = newVal;
            };
            vm.saveCertainty = function(certainty) {
                vm.updateCertainty(certainty);
            };
            vm.saveCertaintyAndClose = function(certainty) {
                vm.updateCertainty(certainty);
                vm.onClose({reason: true});
            };
        }]
});

spacialistApp.component('geodata', {
    bindings: {
        map: '<',
        geodataId: '<',
        onSetGeodata: '&'
    },
    controller: 'geodataCtrl',
    template: '',
});

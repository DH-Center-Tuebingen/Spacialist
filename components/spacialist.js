spacialistApp.component('spacialist', {
    bindings: {
        tab: '@',
        editMode: '<',
        contexts: '<',
        user: '<',
        concepts:'<',
        menus: '<',
        map: '<',
        layer: '<',
        geodata: '<',
        contextTypes: '<',
        files: '<',
        availableTags: '<'
    },
    templateUrl: 'view.html',
    controller: 'mainCtrl'
});

spacialistApp.component('spacialistdata', {
    bindings: {
        context: '<',
        data: '<',
        fields: '<',
        sources: '<',
        menus: '<',
        geodate: '<',
        user: '<',
        concepts: '<',
        onStore: '&',
        onSourceAdd: '&'
    },
    templateUrl: 'templates/context-data.html',
    controller: 'contextCtrl'
});

spacialistApp.component('sourcemodal', {
        templateUrl: "modals/sources.html",
        bindings: {
            attribute: '<',
            certainty: '<',
            attribute_sources: '<',
            context: '<',
            literature: '<',
            sources: '<',
            onAdd: '&',
            onClose: '&',
            onDismiss: '&'
        },
        controller: ['$scope', 'snackbarService', 'httpPutFactory', '$translate', function($scope, snackbarService, httpPutFactory, $translate) {
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
                vm.onAdd({entry: entry});
                // var formData = new FormData();
                // formData.append('cid', context.id);
                // formData.append('aid', attribute.id);
                // formData.append('lid', entry.source.id);
                // formData.append('desc', entry.desc);
                // httpPostFactory('api/source', formData, function(response) {
                //     console.log($state.$current.parent);
                //     sources.push(response.source);
                //     entry.source = undefined;
                //     entry.desc = '';
                // });
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

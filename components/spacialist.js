spacialistApp.component('spacialist', {
    bindings: {
        contexts: '<',
        user: '<',
        concepts:'<',
        map: '<',
        layer: '<',
        geodata: '<',
        contextTypes: '<'
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
        geodate: '<',
        user: '<',
        concepts: '<',
        onStore: '&'
    },
    templateUrl: 'templates/context-data.html',
    controller: 'contextCtrl'
});

spacialistApp.component('sourcemodal', {
        bindings: {
            // attribute: '<',
            // certainty: '<',
            // concepts: '<',
            // literature: '<',
            // attribute_sources: '<',
            sources: '<',
            // onEvent: '&'
            resolve: '<'
        },
        templateUrl: "modals/sources.html",
        controller: ['$scope', function($scope) {
            console.log(this.sources);
            console.log(this.resolve);
            $scope.attribute = this.attribute;
            $scope.certainty = this.certainty;
            $scope.concepts = this.concepts;
            $scope.attribute_sources = this.attribute_sources;
            $scope.literature = this.literature;
            $scope.newEntry = {
                source: '',
                desc: ''
            };

            var updateCertainty = function(certainty) {
                var formData = new FormData();
                formData.append('possibility', certainty.certainty);
                if(certainty.description) formData.append('possibility_description', certainty.description);
                httpPutFactory('api/context/attribute_value/'+context.id+'/'+attribute.id, formData, function(callback) {
                    var content = $translate.instant('snackbar.data-stored.success');
                    snackbarService.addAutocloseSnack(content, 'success');
                });
            };

            $scope.cancel = function() {
                $scope.$dismiss();
            };
            $scope.addSource = function(entry) {
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
                this.onEvent();
            };
            $scope.setCertainty = function(event, certainty) {
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
            $scope.saveCertainty = function(certainty) {
                updateCertainty(certainty);
            };
            $scope.saveCertaintyAndClose = function(certainty) {
                updateCertainty(certainty);
                $scope.$close(true);
            };
        }]
});

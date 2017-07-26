spacialistApp.controller('contextCtrl', ['$scope', 'mainService', '$translate', function($scope, mainService, $translate) {
    this.store = function(data) {
        this.onStore({context: this.context, data: data});
    };
}]);

spacialistApp.controller('contextCtrl', ['$scope', 'mainService', '$translate', function($scope, mainService, $translate) {
    this.editContext = angular.copy(this.context);

    this.store = function(data) {
        this.onStore({context: this.editContext, data: data});
    };

    this.addSource = function(entry) {
        this.onSourceAdd({entry: entry});
    };
}]);

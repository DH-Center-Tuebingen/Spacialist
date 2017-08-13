spacialistApp.controller('contextCtrl', ['$scope', 'mainService', function($scope, mainService) {
    this.currentElement = mainService.currentElement;
    this.editContext = angular.copy(this.context);

    this.store = function(data) {
        this.onStore({context: this.editContext, data: data});
    };

    this.addSource = function(entry) {
        this.onSourceAdd({entry: entry});
    };
}]);

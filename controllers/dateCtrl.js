spacialistApp.controller('dateCtrl', ['$scope', 'scopeService', function($scope, scopeService) {
    if(typeof scopeService.datetable === 'undefined') {
        $scope.datetable = [];
        scopeService.updateDates($scope.datetable);
    } else {
        $scope.datetable = scopeService.datetable;
    }
    $scope.min = -1000;
    $scope.max = 1000;
}]);

/*
 * returns all epochs where start or end is between the given min and max value
 */
spacialistApp.filter('rangeFilter', function() {
    return function(dates, min, max) {
        var filtered = [];
        angular.forEach(dates, function(date) {
            var lower = parseInt(date.start_in);
            var upper = parseInt(date.stop_in);
            if(lower >= min && lower <= max && upper >= min && upper <= max) {
                filtered.push(date);
            }
        });
        return filtered;
    }
});

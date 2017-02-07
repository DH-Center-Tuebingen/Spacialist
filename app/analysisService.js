spacialistApp.service('analysisService', ['httpGetFactory', function(httpGetFactory) {
    var analysis = {};

    analysis.getStoredQueries = function() {
        httpGetFactory('api/analysis/queries/getAll', function(queries) {
            analysis.storedQueries = queries;
        });
    };

    return analysis;
}]);

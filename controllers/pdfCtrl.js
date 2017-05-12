spacialistApp.controller('pdfCtrl', ['$scope', 'httpGetFactory', 'pdfDelegate', function($scope, httpGetFactory, pdfDelegate) {
    $scope.fileLoaded = false;
    $scope.isPreview = false;

    var pdfHandle = 'pdf-handle';
    var pdfDelegateHandle = pdfDelegate.$getByHandle(pdfHandle);

    $scope.togglePreview = function() {
        $scope.isPreview = !$scope.isPreview;
    };

    // Delegate functions
    $scope.prev = function() {
        return pdfDelegateHandle.prev();
    };
    $scope.next = function() {
        return pdfDelegateHandle.next();
    };
    $scope.zoomIn = function(amount) {
        return pdfDelegateHandle.zoomIn(amount);
    };
    $scope.zoomOut = function(amount) {
        return pdfDelegateHandle.zoomOut(amount);
    };
    $scope.zoomTo = function(amount) {
        return pdfDelegateHandle.zoomTo(amount);
    };
    $scope.rotate = function() {
        return pdfDelegateHandle.rotate();
    };
    $scope.getPageCount = function() {
        return pdfDelegateHandle.getPageCount();
    };
    $scope.getCurrentPage = function() {
        return pdfDelegateHandle.getCurrentPage();
    };
    $scope.goToPage = function(pageNumber) {
        return pdfDelegateHandle.goToPage(pageNumber);
    };
    $scope.load = function() {
        return pdfDelegateHandle.load();
    };
}]);

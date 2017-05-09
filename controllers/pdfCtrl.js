spacialistApp.controller('pdfCtrl', ['$scope', 'httpGetFactory', 'pdfDelegate', function($scope, httpGetFactory, pdfDelegate) {
    $scope.fileLoaded = false;
    $scope.isPreview = false;

    var pdfHandle = 'pdf-handle';
    var pdfDelegateHandle = pdfDelegate.$getByHandle(pdfHandle);

    $scope.getFileContent = function(img, decodeBase64) {
        $scope.fileLoaded = false;
        var url = 'api/image/get/' + img.id;
        if(decodeBase64) url += '/decoded';
        httpGetFactory(url, function(response) {
            $scope.fileContent = response;
            $scope.fileLoaded = true;
        });
    };

    $scope.togglePreview = function() {
        $scope.isPreview = !$scope.isPreview;
        if($scope.isPreview) {
            $scope.previewContent = marked($scope.fileContent);
        } else {
            $scope.previewContent = '';
        }
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

spacialistApp.controller('imageCtrl', function($scope, scopeService, modalService, httpGetFactory, Upload, $timeout) {
    $scope.imageList = [];
    scopeService.imageList = $scope.imageList;

    $scope.setImageTab = function(to) {
        $scope.layerTwo.activeImageTab = to;
        $scope.imageList = [];
        $scope.loadImages();
    };

    /**
     * Opens a modal for a given image `img`. This modal displays a zoomable image container and other relevant information of the image
     */
    $scope.openImageModal = function(img, filmIndex) {
        modalOptions = {};
        modalOptions.markers = angular.extend({}, scopeService.markers);
        modalOptions.img = angular.extend({}, img);
        modalOptions.linkImage = $scope.linkImage;
        modalOptions.unlinkImage = $scope.unlinkImage;
        modalOptions.isEmpty = $scope.isEmpty;
        modalOptions.modalNav = angular.extend({}, $scope.modalNav);
        modalService.showModal({}, modalOptions);
    };

    /**
     * enables drag & drop support for image upload, calls `$scope.uploadImages` if files are dropped on the `dropFiles` model
     */
    $scope.$watch('dropFiles', function() {
        $scope.uploadImages($scope.dropFiles);
    });

    /**
     * Upload the image files `files` to the server, one by one and store their paths in the database.
     */
    $scope.uploadImages = function(files, invalidFiles) {
        var finished = 0;
        var toFinish = (typeof files === 'undefined') ? 0 : files.length;
        $scope.uploadingImages = files;
        $scope.errFiles = invalidFiles;
        var responseF = function(response) {
            $timeout(function() {
                console.log(response);
                var data = response.data;
                data.linked = [];
                if (typeof $scope.allImages === 'undefined') $scope.allImages = [];
                $scope.allImages.push(data);
                finished++;
                if (finished == toFinish) {
                    $scope.uploadingImages = undefined;
                }
            });
        };
        var errorF = function(response) {
            if (response.status > 0)
                $scope.errorMsg = response.status + ': ' + response.data;
        };
        var updateF = function(evt) {
            file.progress = Math.min(100, parseInt(100.0 *
                evt.loaded / evt.total));
        };

        for(var i=0; i<files.length; i++) {
            var file = files[i];
            file.upload = Upload.upload({
                url: 'api/image/upload',
                data: {
                    file: file
                }
            });
            file.upload.then(responseF, errorF, updateF);
        }
    };

    $scope.loadImages = function() {
        var imageSrc;
        if($scope.layerTwo.activeImageTab == 'all') {
            imageSrc = $scope.allImages;
        } else {
             imageSrc = $scope.linkedImages;
        }
        var len = $scope.imageList.length;
        if (len == imageSrc.length) return;
        $scope.imageList = [].concat($scope.imageList, imageSrc.slice(len, len + 10));
    };

    var getAllImages = function() {
        httpGetFactory('api/image/getAll', function(callback) {
            var images = [];
            angular.forEach(callback, function(value, key) {
                value.linked = [];
                images.push(value);
            });
            angular.extend($scope.allImages, images);
            $scope.linkedImages = [];
        });
    };

    getAllImages();
});

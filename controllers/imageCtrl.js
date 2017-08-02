spacialistApp.controller('imageCtrl', ['$scope', 'imageService', 'mainService', 'snackbarService', '$translate', function($scope, imageService, mainService, snackbarService, $translate) {
    $scope.images = {};
    $scope.images.all = imageService.all;
    $scope.images.linked = imageService.linked;
    $scope.currentElement = mainService.currentElement;
    $scope.upload = imageService.upload;
    $scope.tags = imageService.tags;
    $scope.hasMoreImages = imageService.hasMoreImages;
    $scope.getMimeType = imageService.getMimeType;
    $scope.search = {
        terms: {
            tags: []
        }
    };

    $scope.addTag = function(img, item) {
        imageService.addTag(img, item);
    };

    $scope.removeTag = function(img, item) {
        imageService.removeTag(img, item);
    };

    var linkImageContextMenu = [function($itemScope) {
        var f = $itemScope.f;
        var content;
        for(var i=0; i<f.linked_images.length; i++) {
            if(f.linked_images[i].context_id == mainService.currentElement.element.id) {
                content = $translate.instant('photo.unlink-from', { name: mainService.currentElement.element.name });
                break;
            }
        }
        if(!content) {
            content = $translate.instant('photo.link-to', { name: mainService.currentElement.element.name });
        }
        return '<i class="material-icons md-18">add_circle_outline</i> ' + content;
    }, function ($itemScope) {
        var f = $itemScope.f;
        var fileId = f.id;
        var contextId = mainService.currentElement.element.id;
        for(var i=0; i<f.linked_images.length; i++) {
           if(f.linked_images[i].context_id == mainService.currentElement.element.id) {
               imageService.unlinkImage(fileId, contextId);
               return;
           }
        }
        imageService.linkImage(fileId, contextId);
    }, function() {
        return mainService.currentElement.element.id > 0;
    }];
    var contextMenuSearch = [function() {
        var content = $translate.instant('photo.context-search');
        return '<i class="material-icons md-18">search</i> ' + content;
    }, function ($itemScope, $event, modelValue, text, $li) {
       //TODO implement (open modal with search field or inline)
    }];
    var deleteImage = [function($itemScope) {
        var content = $translate.instant('photo.delete', { name: $itemScope.f.filename });
       return '<i class="material-icons md-18">delete</i> ' + content;
    }, function ($itemScope, $event, modelValue, text, $li) {
       imageService.deleteImage($itemScope.f);
    }];

    $scope.imageContextMenu = [
        linkImageContextMenu,
        null,
        deleteImage,
        contextMenuSearch
    ];

    /**
     * Opens a modal for a given image `img`. This modal displays a zoomable image container and other relevant information of the image
     */
    $scope.openImageModal = function(img) {
        imageService.openImageModal(img);
    };

    /**
     * enables drag & drop support for image upload, calls `$scope.uploadImages` if files are dropped on the `dropFiles` model
     */
    $scope.$watch('dropFiles', function() {
        imageService.uploadImages($scope.dropFiles);
    });

    $scope.uploadImages = function($files, $invalidFiles) {
        imageService.uploadImages($files, $invalidFiles);
    };
}]);

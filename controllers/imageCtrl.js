spacialistApp.controller('imageCtrl', ['$scope', 'imageService', 'mainService', 'snackbarService', '$translate', function($scope, imageService, mainService, snackbarService, $translate) {
    $scope.images = {};
    $scope.images.all = imageService.all;
    $scope.images.linked = imageService.linked;
    $scope.currentElement = mainService.currentElement;
    $scope.upload = imageService.upload;
    $scope.tags = imageService.tags;
    $scope.hasMoreImages = imageService.hasMoreImages;
    $scope.search = {
        terms: {
            tags: []
        }
    };

    $scope.initImageTab = function() {
        $scope.layerTwo.imageTab.newOpen = false;
        if($scope.currentElement.element.id) {
            $scope.layerTwo.imageTab.linkedOpen = true;
            $scope.layerTwo.imageTab.allOpen = false;
            imageService.getLinkedImages($scope.currentElement.element.id);
        } else {
            $scope.layerTwo.imageTab.linkedOpen = false;
            $scope.layerTwo.imageTab.allOpen = true;
            imageService.getAllImages();
        }
    };

    $scope.addTag = function(img, item) {
        imageService.addTag(img, item);
    };

    $scope.removeTag = function(img, item) {
        imageService.removeTag(img, item);
    };

    $scope.getMimeType = function(mt, f) {
        return imageService.getMimeType(mt, f);
    };

    var linkImageContextMenu = [function($itemScope) {
        var img = $itemScope.img;
        var content;
        for(var i=0; i<img.linked_images.length; i++) {
            if(img.linked_images[i].context_id == mainService.currentElement.element.id) {
                content = $translate.instant('photo.unlink-from', { name: mainService.currentElement.element.name });
                break;
            }
        }
        if(!content) {
            content = $translate.instant('photo.link-to', { name: mainService.currentElement.element.name });
        }
        return '<i class="material-icons md-18">add_circle_outline</i> ' + content;
    }, function ($itemScope) {
        var img = $itemScope.img;
        var imgId = img.id;
        var contextId = mainService.currentElement.element.id;
        for(var i=0; i<img.linked_images.length; i++) {
           if(img.linked_images[i].context_id == mainService.currentElement.element.id) {
               imageService.unlinkImage(imgId, contextId);
               return;
           }
        }
        imageService.linkImage(imgId, contextId);
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
        var content = $translate.instant('photo.delete', { name: $itemScope.img.filename });
       return '<i class="material-icons md-18">delete</i> ' + content;
    }, function ($itemScope, $event, modelValue, text, $li) {
       imageService.deleteImage($itemScope.img);
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

    $scope.loadImages = function(len, type) {
        var imgs = imageService.loadImages(len, type);
        return imgs;
    };

    $scope.updateLinkedImages = function(beforeClick) {
        //if it was closed before toggle
        if(!beforeClick) {
            imageService.getLinkedImages($scope.currentElement.element.id);
        }
    };

    $scope.updateAllImages = function(beforeClick) {
        //if it was closed before toggle
        if(!beforeClick) {
            imageService.getAllImages();
        }
    };
}]);

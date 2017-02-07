spacialistApp.controller('imageCtrl', ['$scope', 'imageService', 'mainService', function($scope, imageService, mainService) {
    $scope.images = {};
    $scope.images.all = imageService.all;
    $scope.images.linked = imageService.linked;
    $scope.currentElement = mainService.currentElement;

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

    var linkImageContextMenu = [function() {
       var dflt = '<i class="fa fa-fw fa-plus-circle"></i> Mit aktuellem Kontext verbinden';
       //get element by dom, because $scope seems to be the isolated template (image-list.html) scope
       var tmpScope =  angular.element(document.getElementsByClassName('selected-leaf-child')[0]).scope();
       if(tmpScope) {
           var currentElement = tmpScope.element;
           dflt += ' <i style="opacity: 0.5;">' + currentElement.name + '</i>';
       }
       return dflt;
    }, function ($itemScope, $event, modelValue, text, $li) {
       var tmpScope =  angular.element(document.getElementsByClassName('selected-leaf-child')[0]).scope();
       if(!tmpScope) return;
       var currentElement = tmpScope.element;
       var imgId = $itemScope.img.id;
       var contextId = currentElement.id;
       imageService.linkImage(imgId, contextId);
    }, function() {
       var tmpScope =  angular.element(document.getElementsByClassName('selected-leaf-child')[0]).scope();
       if(!tmpScope) return false;
       return typeof tmpScope.element != 'undefined';
    }];
    var unlinkImageContextMenu = [function() {
       var dflt = '<i class="fa fa-fw fa-plus-circle"></i> Von aktuellem Kontext lösen';
       //get element by dom, because $scope seems to be the isolated template (image-list.html) scope
       var tmpScope =  angular.element(document.getElementsByClassName('selected-leaf-child')[0]).scope();
       if(tmpScope) {
           var currentElement = tmpScope.element;
           dflt += ' <i style="opacity: 0.5;">' + currentElement.name + '</i>';
       }
       return dflt;
    }, function ($itemScope, $event, modelValue, text, $li) {
       var tmpScope =  angular.element(document.getElementsByClassName('selected-leaf-child')[0]).scope();
       if(!tmpScope) return;
       var currentElement = tmpScope.element;
       var imgId = $itemScope.img.id;
       var contextId = currentElement.id;
       imageService.unlinkImage(imgId, contextId);
    }, function() {
       var tmpScope =  angular.element(document.getElementsByClassName('selected-leaf-child')[0]).scope();
       if(!tmpScope) return false;
       return typeof tmpScope.element != 'undefined';
    }];
    var contextMenuSearch = ['<i class="fa fa-fw fa-search"></i> Nach Kontexten suchen', function ($itemScope, $event, modelValue, text, $li) {
       //TODO implement (open modal with search field or inline)
    }];

    $scope.imageContextMenu = {
       all: [
           linkImageContextMenu,
           null,
           contextMenuSearch
       ],
       linked: [
           unlinkImageContextMenu,
           null,
           contextMenuSearch
       ]
    };

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

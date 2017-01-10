spacialistApp.controller('imageCtrl', function($rootScope, $scope, scopeService, modalService, httpGetFactory, httpPostFactory, Upload, $timeout) {
    $scope.images = {
        linked: [],
        all: []
    };
    scopeService.images = $scope.images;

    $scope.initImageTab = function() {
        $scope.layerTwo.imageTab.newOpen = false;
        if($scope.currentElement) {
            $scope.layerTwo.imageTab.linkedOpen = true;
            $scope.layerTwo.imageTab.allOpen = false;
            getLinkedImages($scope.currentElement.id);
        } else {
            $scope.layerTwo.imageTab.linkedOpen = false;
            $scope.layerTwo.imageTab.allOpen = true;
            getAllImages();
        }
    };

    $scope.imageContextMenu = [
        [function() {
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
            linkImage(imgId, contextId);
        }, function() {
            var tmpScope =  angular.element(document.getElementsByClassName('selected-leaf-child')[0]).scope();
            if(!tmpScope) return false;
            return typeof tmpScope.element != 'undefined';
        }],
        null,
        ['<i class="fa fa-fw fa-search"></i> Nach Kontexten suchen', function ($itemScope, $event, modelValue, text, $li) {
            //TODO implement (open modal with search field or inline)
        }]
    ];

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
                if (typeof $scope.images.all === 'undefined') $scope.images.all = [];
                $scope.images.all.push(data);
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

    /**
     * Link the image with ID `imgId` to the context with the id `contextId`
     */
    var linkImage = function(imgId, contextId) {
        var formData = new FormData();
        formData.append('imgId', imgId);
        formData.append('ctxId', contextId);
        httpPostFactory('api/image/link', formData, function(response) {
            console.log("image " + imgId + " is now linked to " + contextId);
        });
    };

    var unlinkImage = function(imgId, contextId) {
        var formData = new FormData();
        formData.append('imgId', imgId);
        formData.append('ctxId', contextId);
        httpPostFactory('api/image/unlink', formData, function(response) {
            console.log("unlinked image " + imgId + " from " + contextId);
        });
    };

    $scope.loadImages = function(len, type) {
        var src = $scope.images[type];
        if(len == src.length) return src;
        var loaded = src.slice(0, len + 10);
        return loaded;
    };

    $scope.updateLinkedImages = function(beforeClick) {
        //if it was closed before toggle
        if(!beforeClick) {
            getLinkedImages($scope.currentElement.id);
        }
    };

    $scope.updateAllImages = function(beforeClick) {
        //if it was closed before toggle
        if(!beforeClick) {
            getAllImages();
        }
    };

    scopeService.getImagesForContext = function(id) {
        $rootScope.$emit('image:delete:linked');
        $scope.images.linked = [];
        getLinkedImages(id);
    };

    var getLinkedImages = function(id) {
        httpGetFactory('api/image/getByContext/' + id, function(response) {
            var oneUpdated = false;
            var linkedCopy = $scope.images.linked.slice();
            for(var i=0; i<response.images.length; i++) {
                var newLinked = response.images[i];
                var alreadyLinked = false;
                for(var j=0; j<linkedCopy.length; j++) {
                    var linked = linkedCopy[j];
                    if(newLinked.id == linked.id) {
                        if(!angular.equals(newLinked, linked)) {
                            oneUpdated = true;
                            $scope.images.linked[j] = newLinked;
                        }
                        alreadyLinked = true;
                        break;
                    }
                }
                if(!alreadyLinked) {
                    oneUpdated = true;
                    $scope.images.linked.push(newLinked);
                }
            }
            if(oneUpdated) {
                $rootScope.$emit('image:updated:linked');
            }
        });
    };

    var getAllImages = function(forceUpdate) {
        forceUpdate = forceUpdate || false;
        var currentTime = (new Date()).getTime();
        //only fetch images again if it is a force update or last time checked is > 60s
        if(!forceUpdate && currentTime - $scope.lastTimeImageChecked <= 60000) {
            return;
        } else {
            httpGetFactory('api/image/getAll', function(response) {
                var oneUpdated = false;
                var allCopy = $scope.images.all.slice();
                for(var i=0; i<response.length; i++) {
                    var newImg = response[i];
                    var alreadyLinked = false;
                    for(var j=0; j<allCopy.length; j++) {
                        var one = allCopy[j];
                        if(newImg.id == one.id) {
                            if(!angular.equals(newImg, one)) {
                                oneUpdated = true;
                                $scope.images.all[j] = newImg;
                            }
                            alreadyLinked = true;
                            break;
                        }
                    }
                    if(!alreadyLinked) {
                        oneUpdated = true;
                        $scope.images.all.push(newImg);
                    }
                }
                if(oneUpdated) {
                    $rootScope.$emit('image:updated:all');
                }
            });
        }
    };
});

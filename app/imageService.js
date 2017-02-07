spacialistApp.service('imageService', ['$rootScope', 'httpPostFactory', 'httpGetFactory', 'modalService', 'Upload', '$timeout', function($rootScope, httpPostFactory, httpGetFactory, modalService, Upload, $timeout) {
    var images = {
        all: [],
        linked: [],
        upload: {}
    };

    var lastTimeImageChecked = 0;

    images.openImageModal = function(img) {
        modalOptions = {};
        // modalOptions.markers = angular.extend({}, scopeService.markers);
        modalOptions.img = angular.extend({}, img);
        modalOptions.linkImage = images.linkImage;
        modalOptions.unlinkImage = images.unlinkImage;
        // modalOptions.isEmpty = $scope.isEmpty;
        // modalOptions.modalNav = angular.extend({}, $scope.modalNav);
        modalService.showModal({}, modalOptions);
    };

    images.getImagesForContext = function(id) {
        if(!id) return;
        $rootScope.$emit('image:delete:linked');
        images.linked = [];
        images.getLinkedImages(id);
    };

    images.getLinkedImages = function(id) {
        if(!id) return;
        httpGetFactory('api/image/getByContext/' + id, function(response) {
            var oneUpdated = false;
            var linkedCopy = images.linked.slice();
            for(var i=0; i<response.images.length; i++) {
                var newLinked = response.images[i];
                var alreadyLinked = false;
                for(var j=0; j<linkedCopy.length; j++) {
                    var linked = linkedCopy[j];
                    if(newLinked.id == linked.id) {
                        if(!angular.equals(newLinked, linked)) {
                            oneUpdated = true;
                            images.linked[j] = newLinked;
                        }
                        alreadyLinked = true;
                        break;
                    }
                }
                if(!alreadyLinked) {
                    oneUpdated = true;
                    images.linked.push(newLinked);
                }
            }
            if(oneUpdated) {
                $rootScope.$emit('image:updated:linked');
            }
        });
    };

    images.loadImages = function(len, type) {
        var src = images[type];
        if(len == src.length) return src;
        var loaded = src.slice(0, len + 10);
        return loaded;
    };

    /**
     * Upload the image files `files` to the server, one by one and store their paths in the database.
     */
    images.uploadImages = function(files, invalidFiles) {
        var finished = 0;
        var toFinish = (typeof files === 'undefined') ? 0 : files.length;
        images.uploadingImages = files;
        images.errFiles = invalidFiles;
        var responseF = function(response) {
            $timeout(function() {
                console.log(response);
                var data = response.data;
                data.linked = [];
                if (typeof images.all === 'undefined') images.all = [];
                images.all.push(data);
                finished++;
                if (finished == toFinish) {
                    images.uploadingImages = undefined;
                }
            });
        };
        var errorF = function(response) {
            if(response.status > 0)
                images.upload.errorMsg = response.status + ': ' + response.data;
        };
        var updateF = function(evt) {
            file.progress = Math.min(100, parseInt(100.0 *
                evt.loaded / evt.total));
        };

        if(files) {
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
        }
    };

    /**
     * Link the image with ID `imgId` to the context with the id `contextId`
     */
    images.linkImage = function(imgId, contextId) {
        var formData = new FormData();
        formData.append('imgId', imgId);
        formData.append('ctxId', contextId);
        httpPostFactory('api/image/link', formData, function(response) {
            console.log("image " + imgId + " is now linked to " + contextId);
        });
    };

    images.unlinkImage = function(imgId, contextId) {
        var formData = new FormData();
        formData.append('imgId', imgId);
        formData.append('ctxId', contextId);
        httpPostFactory('api/image/unlink', formData, function(response) {
            console.log("unlinked image " + imgId + " from " + contextId);
            images.getImagesForContext(imgId);
        });
    };

    images.getAllImages = function(forceUpdate) {
        forceUpdate = forceUpdate || false;
        var currentTime = (new Date()).getTime();
        //only fetch images again if it is a force update or last time checked is > 60s
        if(!forceUpdate && currentTime - lastTimeImageChecked <= 60000) {
            return;
        } else {
            lastTimeImageChecked = currentTime;
            httpGetFactory('api/image/getAll', function(response) {
                var oneUpdated = false;
                var allCopy = images.all.slice();
                for(var i=0; i<response.length; i++) {
                    var newImg = response[i];
                    var alreadyLinked = false;
                    for(var j=0; j<allCopy.length; j++) {
                        var one = allCopy[j];
                        if(newImg.id == one.id) {
                            if(!angular.equals(newImg, one)) {
                                oneUpdated = true;
                                images.all[j] = newImg;
                            }
                            alreadyLinked = true;
                            break;
                        }
                    }
                    if(!alreadyLinked) {
                        oneUpdated = true;
                        images.all.push(newImg);
                    }
                }
                if(oneUpdated) {
                    $rootScope.$emit('image:updated:all');
                }
            });
        }
    };

    return images;
}]);

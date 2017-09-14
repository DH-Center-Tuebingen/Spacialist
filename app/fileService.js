spacialistApp.service('fileService', ['$rootScope', 'httpPostFactory', 'httpGetFactory', 'httpGetPromise', 'httpPutFactory', 'httpPatchFactory', 'httpDeleteFactory', 'snackbarService', 'searchService', 'Upload', '$translate', '$timeout', '$state', function($rootScope, httpPostFactory, httpGetFactory, httpGetPromise, httpPutFactory, httpPatchFactory, httpDeleteFactory, snackbarService, searchService, Upload, $translate, $timeout, $state) {
    var images = {
        all: [],
        linked: [],
        upload: {}
    };

    images.tags = {
        visible: false
    };

    var lastTimeImageChecked = 0;

    images.getMimeType = function(f) {
        if(!f) return;
        // check for extension before mime-type check
        var filename = f.filename;
        var mt = f.mime_type;
        if(filename) {
            var suffix = filename.substr(filename.lastIndexOf('.')+1);
            switch(suffix) {
                case 'dae':
                case 'obj':
                    return '3d';
            }
        }

        if(mt.startsWith('image/')) return 'image';
        if(mt == 'application/pdf') return 'pdf';
        if(mt == 'application/xml' || mt == 'text/xml') return 'xml';
        if(mt == 'application/xhtml+xml' || mt == 'text/html') return 'html';
        if(mt.startsWith('audio/')) return 'audio';
        if(mt.startsWith('video/')) return 'video';

        // default is simple text
        return 'text';
    };

    images.getImagesForContext = function(id) {
        if(!id) return;
        $rootScope.$emit('image:delete:linked');
        images.linked = [];
        images.getLinkedImages(id);
    };

    images.getLinkedImages = function(id) {
        if(!id) return;
        images.linked = filterLinkedImages(id);
    };

    images.loadImages = function(len, type) {
        var src = images[type];
        if(len == src.length) return src;
        var loaded = src.slice(0, len + 10);
        return loaded;
    };

    images.hasMoreImages = function(len, type) {
        var src = images[type];
        return src.length - len;
    };

    /**
     * Upload the image files `files` to the server, one by one and store their paths in the database.
     */
    images.uploadImages = function(files, invalidFiles, fileList) {
        var upload = {};
        upload.files = files;
        upload.errFiles = invalidFiles;
        upload.finished = 0;
        upload.toFinish = (typeof files === 'undefined') ? 0 : files.length;
        angular.forEach(files, function(file) {
            file.upload = Upload.upload({
                url: 'api/image/upload',
                data: { file: file }
            });
            file.upload.then(function(response) {
                $timeout(function() {
                    var data = response.data;
                    data.linked = [];
                    fileList.push(data);
                    upload.finished++;
                    if (upload.finished == upload.toFinish) {
                        $timeout(function() {
                            upload.files.length = 0;
                        }, 1000);
                        var content = $translate.instant('snackbar.image-upload.success', {
                            cnt: upload.finished
                        });
                        snackbarService.addAutocloseSnack(content, 'success');
                    }
                });
            }, function(response) {
                if(response.status > 0) {
                    file.errorMsg = response.status + ': ' + response.data;
                }
            }, function(evt) {
                file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
            });
        });
        return upload;
    };

    /**
     * Link the image with ID `imgId` to the context with the id `contextId`
     */
    images.linkImage = function(imgId, contextId) {
        var formData = new FormData();
        formData.append('imgId', imgId);
        formData.append('ctxId', contextId);
        httpPutFactory('api/image/link', formData, function(response) {
            var content = $translate.instant('snackbar.image-linked.success');
            snackbarService.addAutocloseSnack(content, 'success');
            $state.reload('root.spacialist');
        });
    };

    images.unlinkImage = function(imgId, contextId) {
        httpDeleteFactory('api/image/link/' + imgId + '/' + contextId, function(response) {
            var content = $translate.instant('snackbar.image-unlinked.success');
            snackbarService.addAutocloseSnack(content, 'success');
            $state.reload('root.spacialist');
        });
    };

    images.deleteFile = function(file, fileList) {
        httpDeleteFactory('api/image/' + file.id, function(response) {
            if(!response.error) {
                var content = $translate.instant('snackbar.image-deleted.success');
                snackbarService.addAutocloseSnack(content, 'success');
                var i = fileList.indexOf(file);
                if(i > -1) fileList.splice(i, 1);
            }
        });
    };

    images.getImages = function() {
        return httpGetPromise.getData('api/image').then(function(response) {
            return response;
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
            httpGetFactory('api/image', function(response) {
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
                                updateSearchOptions(newImg);
                                images.all[j] = newImg;
                            }
                            alreadyLinked = true;
                            break;
                        }
                    }
                    if(!alreadyLinked) {
                        oneUpdated = true;
                        updateSearchOptions(newImg);
                        images.all.push(newImg);
                    }
                }
                if(oneUpdated) {
                    $rootScope.$emit('image:updated:all');
                }
            });
        }
    };

    images.addTag = function(img, tag) {
        var formData = new FormData();
        formData.append('photo_id', img.id);
        formData.append('tag_id', tag.id);
        httpPutFactory('api/image/tag', formData, function(response) {
            if(response.error) {
                // TODO remove from img.tags
                return;
            }
        });
    };

    images.removeTag = function(img, tag) {
        httpDeleteFactory('api/image/' + img.id + '/tag/' + tag.id,  function(response) {
            if(response.error) {
                // TODO add back to img.tags
                return;
            }
        });
    };

    images.getAvailableTags = function() {
        return httpGetPromise.getData('api/image/tag').then(function(response) {
            return response.tags;
        });
    };

    function updateSearchOptions(img) {
        updateTags(img);
        updateDates(img);
        updateCameras(img);
    }

    function updateTags(img) {
        for(var i=0; i<img.tags.length; i++) {
            var tag = img.tags[i];
            var found = false;
            for(var j=0; j<images.availableTags.length; j++) {
                var aTag = images.availableTags[j];
                if(tag.id == aTag.id) {
                    found = true;
                    img.tags[i] = images.availableTags[j];
                }
            }
            if(!found) {
                delete img.tags[i];
            }
        }
    }

    function updateDates(img) {
        var dateTerms = searchService.availableSearchTerms.dates;
        var createdDay = searchService.formatUnixDate(img.created*1000);
        if(dateTerms.indexOf(createdDay) == -1) {
            dateTerms.push(createdDay);
            dateTerms.sort();
        }
    }

    function updateCameras(img) {
        var camTerms = searchService.availableSearchTerms.cameras;
        if(camTerms.indexOf(img.cameraname) == -1) {
            camTerms.push(img.cameraname);
            camTerms.sort();
        }
    }

    function filterLinkedImages(id) {
        return images.all.filter(function(img) {
            for(var i=0; i<img.linked_images.length; i++) {
                if(img.linked_images[i].context_id == id) {
                    return true;
                }
            }
            return false;
        });
    }

    return images;
}]);

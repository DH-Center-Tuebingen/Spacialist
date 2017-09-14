spacialistApp.service('fileService', ['$rootScope', 'httpPostFactory', 'httpGetFactory', 'httpGetPromise', 'httpPutFactory', 'httpPatchFactory', 'httpDeleteFactory', 'snackbarService', 'searchService', 'Upload', '$translate', '$timeout', '$state', function($rootScope, httpPostFactory, httpGetFactory, httpGetPromise, httpPutFactory, httpPatchFactory, httpDeleteFactory, snackbarService, searchService, Upload, $translate, $timeout, $state) {
    var files = {
        all: [],
        linked: [],
        upload: {}
    };

    files.tags = {
        visible: false
    };

    var lastTimeImageChecked = 0;

    files.getMimeType = function(f) {
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

    files.getImagesForContext = function(id) {
        if(!id) return;
        $rootScope.$emit('image:delete:linked');
        files.linked = [];
        files.getLinkedImages(id);
    };

    files.getLinkedImages = function(id) {
        if(!id) return;
        files.linked = filterLinkedImages(id);
    };

    files.loadImages = function(len, type) {
        var src = files[type];
        if(len == src.length) return src;
        var loaded = src.slice(0, len + 10);
        return loaded;
    };

    files.hasMoreImages = function(len, type) {
        var src = files[type];
        return src.length - len;
    };

    /**
     * Upload the image files `files` to the server, one by one and store their paths in the database.
     */
    files.uploadImages = function(files, invalidFiles, fileList) {
        var upload = {};
        upload.files = files;
        upload.errFiles = invalidFiles;
        upload.finished = 0;
        upload.toFinish = (typeof files === 'undefined') ? 0 : files.length;
        angular.forEach(files, function(file) {
            file.upload = Upload.upload({
                url: 'api/file/upload',
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
                        var content = $translate.instant('snackbar.file-upload.success', {
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
    files.linkImage = function(imgId, contextId) {
        var formData = new FormData();
        formData.append('imgId', imgId);
        formData.append('ctxId', contextId);
        httpPutFactory('api/file/link', formData, function(response) {
            var content = $translate.instant('snackbar.file-linked.success');
            snackbarService.addAutocloseSnack(content, 'success');
            $state.reload('root.spacialist');
        });
    };

    files.unlinkImage = function(imgId, contextId) {
        httpDeleteFactory('api/file/link/' + imgId + '/' + contextId, function(response) {
            var content = $translate.instant('snackbar.file-unlinked.success');
            snackbarService.addAutocloseSnack(content, 'success');
            $state.reload('root.spacialist');
        });
    };

    files.deleteFile = function(file, fileList) {
        httpDeleteFactory('api/file/' + file.id, function(response) {
            if(!response.error) {
                var content = $translate.instant('snackbar.file-deleted.success');
                snackbarService.addAutocloseSnack(content, 'success');
                var i = fileList.indexOf(file);
                if(i > -1) fileList.splice(i, 1);
            }
        });
    };

    files.getImages = function() {
        return httpGetPromise.getData('api/file').then(function(response) {
            return response;
        });
    };

    files.getAllImages = function(forceUpdate) {
        forceUpdate = forceUpdate || false;
        var currentTime = (new Date()).getTime();
        //only fetch images again if it is a force update or last time checked is > 60s
        if(!forceUpdate && currentTime - lastTimeImageChecked <= 60000) {
            return;
        } else {
            lastTimeImageChecked = currentTime;
            httpGetFactory('api/file', function(response) {
                var oneUpdated = false;
                var allCopy = files.all.slice();
                for(var i=0; i<response.length; i++) {
                    var newImg = response[i];
                    var alreadyLinked = false;
                    for(var j=0; j<allCopy.length; j++) {
                        var one = allCopy[j];
                        if(newImg.id == one.id) {
                            if(!angular.equals(newImg, one)) {
                                oneUpdated = true;
                                updateSearchOptions(newImg);
                                files.all[j] = newImg;
                            }
                            alreadyLinked = true;
                            break;
                        }
                    }
                    if(!alreadyLinked) {
                        oneUpdated = true;
                        updateSearchOptions(newImg);
                        files.all.push(newImg);
                    }
                }
                if(oneUpdated) {
                    $rootScope.$emit('image:updated:all');
                }
            });
        }
    };

    files.addTag = function(img, tag) {
        var formData = new FormData();
        formData.append('photo_id', img.id);
        formData.append('tag_id', tag.id);
        httpPutFactory('api/file/tag', formData, function(response) {
            if(response.error) {
                // TODO remove from img.tags
                return;
            }
        });
    };

    files.removeTag = function(img, tag) {
        httpDeleteFactory('api/file/' + img.id + '/tag/' + tag.id,  function(response) {
            if(response.error) {
                // TODO add back to img.tags
                return;
            }
        });
    };

    files.getAvailableTags = function() {
        return httpGetPromise.getData('api/file/tag').then(function(response) {
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
            for(var j=0; j<files.availableTags.length; j++) {
                var aTag = files.availableTags[j];
                if(tag.id == aTag.id) {
                    found = true;
                    img.tags[i] = files.availableTags[j];
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
        return files.all.filter(function(img) {
            for(var i=0; i<img.linked_images.length; i++) {
                if(img.linked_images[i].context_id == id) {
                    return true;
                }
            }
            return false;
        });
    }

    return files;
}]);

spacialistApp.service('fileService', ['$rootScope', 'httpPostFactory', 'httpGetFactory', 'httpGetPromise', 'httpPutFactory', 'httpPatchFactory', 'httpDeleteFactory', 'snackbarService', 'searchService', 'Upload', '$translate', '$timeout', '$state', function($rootScope, httpPostFactory, httpGetFactory, httpGetPromise, httpPutFactory, httpPatchFactory, httpDeleteFactory, snackbarService, searchService, Upload, $translate, $timeout, $state) {
    var files = {
        all: [],
        linked: [],
        upload: {}
    };

    files.tags = {
        visible: false
    };

    var lastTimeFileChecked = 0;

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
                case 'pdb':
                case 'gltf':
                    return '3d';
                case 'txt':
                case 'md':
                case 'markdown':
                case 'mkd':
                case 'csv':
                case 'json':
                    return 'text';
                case 'zip':
                case 'rar':
                case 'tar':
                case 'tgz':
                case 'gz':
                case 'bz2':
                case 'xz':
                case '7z':
                    return 'zip'
            }
        }

        if(mt.startsWith('image/')) return 'image';
        if(mt == 'application/pdf') return 'pdf';
        if(mt == 'application/xml' || mt == 'text/xml') return 'xml';
        if(mt == 'application/xhtml+xml' || mt == 'text/html') return 'html';
        if(mt == 'application/zip') return 'zip';
        if(mt == 'application/gzip') return 'zip';
        if(mt == 'application/x-tar') return 'zip';
        if(mt == 'application/x-rar-compressed') return 'zip';
        if(mt == 'application/x-bzip') return 'zip';
        if(mt == 'application/x-bzip2') return 'zip';
        if(mt == 'application/x-7z-compressed') return 'zip';
        if(mt.startsWith('audio/')) return 'audio';
        if(mt.startsWith('video/')) return 'video';
        if(mt == 'text/markdown') return 'text';
        if(mt == 'application/json') return 'text';
        if(mt == 'text/csv') return 'text';

        // default is not supported
        return -1;
    };

    files.getFilesForContext = function(id) {
        if(!id) return;
        files.linked = [];
        files.getLinkedFiles(id);
    };

    files.getLinkedFiles = function(id) {
        if(!id) return;
        files.linked = filterLinkedFiles(id);
    };

    files.loadFiles = function(len, type) {
        var src = files[type];
        if(len == src.length) return src;
        var loaded = src.slice(0, len + 10);
        return loaded;
    };

    files.hasMoreFiles = function(len, type) {
        var src = files[type];
        return src.length - len;
    };

    /**
     * Upload the files `files` to the server, one by one and store their paths in the database.
     */
    files.uploadFiles = function(files, invalidFiles, fileList) {
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
     * Link the file with ID `file_id` to the context with the id `contextId`
     */
    files.linkFile = function(fid, contextId) {
        var formData = new FormData();
        formData.append('file_id', fid);
        formData.append('ctxId', contextId);
        httpPutFactory('api/file/link', formData, function(response) {
            var content = $translate.instant('snackbar.file-linked.success');
            snackbarService.addAutocloseSnack(content, 'success');
            $state.reload('root.spacialist');
        });
    };

    files.unlinkFile = function(fid, contextId) {
        httpDeleteFactory('api/file/link/' + fid + '/' + contextId, function(response) {
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

    files.getFiles = function() {
        return httpGetPromise.getData('api/file').then(function(response) {
            return response;
        });
    };

    files.addTag = function(img, tag) {
        var formData = new FormData();
        formData.append('file_id', img.id);
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

    function filterLinkedFiles(id) {
        return files.all.filter(function(img) {
            for(var i=0; i<img.linked_files.length; i++) {
                if(img.linked_files[i].context_id == id) {
                    return true;
                }
            }
            return false;
        });
    }

    return files;
}]);

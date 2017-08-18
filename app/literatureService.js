spacialistApp.service('literatureService', ['modalFactory', 'httpGetPromise', 'httpPostFactory', 'httpPatchPromise', 'httpDeleteFactory', 'snackbarService', '$translate', 'Upload', function(modalFactory, httpGetPromise, httpPostFactory, httpPatchPromise, httpDeleteFactory, snackbarService, $translate, Upload) {
    var literature = {};
    literature.literature = [];
    literature.literatureOptions = {};
    literature.literatureOptions.availableTypes = [
        {
            name: 'article',
            id: 0,
            mandatoryFields: [
                'author', 'title', 'journal', 'year'
            ],
            optionalFields: [
                'volume', 'number', 'pages', 'month', 'note'
            ]
        },
        {
            name: 'book',
            id: 1,
            mandatoryFields: [
                'title', 'publisher', 'year'
            ],
            optionalFields: [
                'author', 'editor', 'volume', 'number', 'address', 'series', 'edition', 'month', 'note'
            ]
        },
        {
            name: 'incollection',
            id: 2,
            mandatoryFields: [
                'author', 'title', 'booktitle', 'publisher', 'year'
            ],
            optionalFields: [
                'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
            ]
        },
        {
            name: 'misc',
            id: 3,
            mandatoryFields: [
            ],
            optionalFields: [
                'author', 'title', 'howpublished', 'month', 'year', 'note'
            ]
        },
        {
            name: 'booklet',
            id: 4,
            mandatoryFields: [
                'title'
            ],
            optionalFields: [
                'author', 'howpublished', 'address', 'month', 'year', 'note'
            ]
        },
        {
            name: 'conference',
            id: 5,
            mandatoryFields: [
                 'author', 'title', 'booktitle', 'year'
            ],
            optionalFields: [
                'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
            ]
        },
        {
            name: 'inbook',
            id: 6,
            mandatoryFields: [
                'title', 'publisher', 'year'
            ],
            optionalFields: [
                'author', 'editor', 'chapter', 'pages', 'volume', 'number', 'series', 'address', 'edition', 'month', 'note'
            ]
        },
        {
            name: 'inproceedings',
            id: 7,
            mandatoryFields: [
                 'author', 'title', 'booktitle', 'year'
            ],
            optionalFields: [
                'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
            ]
        },
        {
            name: 'manual',
            id: 8,
            mandatoryFields: [
                'title'
            ],
            optionalFields: [
                'author', 'organization', 'address', 'edition', 'month', 'year', 'note'
            ]
        },
        {
            name: 'mastersthesis',
            id: 9,
            mandatoryFields: [
                'author', 'title', 'school', 'year'
            ],
            optionalFields: [
                'address', 'month', 'note'
            ]
        },
        {
            name: 'phdthesis',
            id: 10,
            mandatoryFields: [
                'author', 'title', 'school', 'year'
            ],
            optionalFields: [
                'address', 'month', 'note'
            ]
        },
        {
            name: 'proceedings',
            id: 11,
            mandatoryFields: [
                'title', 'year'
            ],
            optionalFields: [
                'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note'
            ]
        },
        {
            name: 'techreport',
            id: 12,
            mandatoryFields: [
                'author', 'title', 'institution', 'year'
            ],
            optionalFields: [
                'number', 'address', 'month', 'note'
            ]
        },
        {
            name: 'unpublished',
            id: 13,
            mandatoryFields: [
                'author', 'title', 'note'
            ],
            optionalFields: [
                'month', 'year'
            ]
        }
    ];

    literature.getAll = function() {
        return httpGetPromise.getData('api/literature').then(function (response) {
            return response;
        });
    };

    literature.getByContext = function(cid) {
        return httpGetPromise.getData('api/source/by_context/' + cid).then(function(response) {
            return response.sources;
        });
    };

    literature.getTypes = function() {
        return literature.literatureOptions.availableTypes;
    };

    literature.deleteLiteratureEntry = function(entry, bibliography) {
        var index = bibliography.indexOf(entry);
        httpDeleteFactory('api/literature/' + entry.id, function(response) {
            bibliography.splice(index, 1);
        });
    };

    literature.openAddLiteratureDialog = function(bibliography) {
        modalFactory.addLiteratureModal(literature.addLiterature, literature.literatureOptions.availableTypes, bibliography);
    };

    literature.importBibTexFile = function(file, invalidFiles, bibliography) {
        if(file) {
            file.upload = Upload.upload({
                 url: 'api/literature/importBibtex',
                 data: { file: file }
            });
            file.upload.then(function(response) {
                if(response.data.error) {
                    modalFactory.errorModal(response.data.error);
                    return;
                }
                var entries = response.data.entries;
                for(var i=0; i<entries.length; i++) {
                    bibliography.push(entries[i]);
                }
                var content = $translate.instant('snackbar.bibtex-upload.success', {
                    cnt: entries.length
                });
                snackbarService.addAutocloseSnack(content, 'success');
            }, function(reponse) {
                if(response.status > 0) {
                    $scope.errorMsg = response.status + ': ' + response.data;
                }
            }, function(evt) {
                file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
            });
        }
    };

    literature.addLiterature = function(fields, type, bibliography) {
        if(typeof type == 'undefined') return;
        if(typeof fields == 'undefined') return;
        var mandatorySet = true;
        for(var i=0; i<type.mandatoryFields.length; i++) {
            var m = type.mandatoryFields[i];
            if(typeof fields[m] == 'undefined') {
                mandatorySet = false;
                break;
            }
            if(fields[m].length === 0) {
                mandatorySet = false;
                break;
            }
        }
        if(!mandatorySet) {
            alert('Not all mandatory fields are set!');
            return;
        }
        var formData = new FormData();
        for(var field in fields) {
            if(fields[field] !== null && fields[field] !== '') {
                if (field == 'id') {
                    continue;
                }
                formData.append(field, fields[field]);
            }
        }
        formData.append('type', type.name);

        httpPostFactory('api/literature', formData, function(response) {
            if(response.error) {
                alert(response.error);
            }
            else {
                bibliography.push(response.literature);
            }
        });
    };

    literature.editLiterature = function(entry, type) {
        if(!type || !entry) return;
        var mandatorySet = true;
        for(var i=0; i<type.mandatoryFields.length; i++) {
            var m = type.mandatoryFields[i];
            if(!entry[m] || entry[m].length === 0) {
                mandatorySet = false;
                break;
            }
        }
        if(!mandatorySet) {
            alert('Not all mandatory fields are set!');
            return;
        }
        var formData = new FormData();
        for(var k in entry) {
            if(entry.hasOwnProperty(k)) {
                if(k == 'id') continue;
                if(entry[k] !== null && entry[k] !== '') {
                    formData.append(k, entry[k]);
                }
            }
        }
        formData.append('type', type.name);
        return httpPatchPromise.getData('api/literature/' + entry.id, formData).then(function(response) {
            if(response.error) {
                alert(response.error);
                return;
            }
            return response.literature;
        });

    };
    return literature;
}]);

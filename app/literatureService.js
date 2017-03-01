spacialistApp.service('literatureService', ['modalFactory', 'httpGetFactory', 'httpGetPromise', 'httpPostFactory', '$http', function(modalFactory, httpGetFactory, httpGetPromise, httpPostFactory, $http) {
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
                'volume', 'number', 'pages'
            ]
        }, {
            name: 'book',
            id: 1,
            mandatoryFields: [
                'author', 'title', 'publisher', 'year'
            ],
            optionalFields: [
                'editor', 'volume', 'number', 'address'
            ]
        }, {
            name: 'incollection',
            id: 2,
            mandatoryFields: [
                'author', 'title', 'booktitle', 'publisher', 'year'
            ],
            optionalFields: [
                'editor', 'volume', 'number', 'pages', 'address'
            ]
        }, {
            name: 'misc',
            id: 3,
            mandatoryFields: [
            ],
            optionalFields: [
                'author', 'title', 'howpublished', 'year'
            ]
        }
    ];

    literature.getLiterature = function() {
        var promise = httpGetFactory('api/literature/getAll', function(response) {
            literature.literature.length = 0;
            angular.forEach(response, function(entry, key) {
                literature.literature.push(entry);
            });
        });
    };

    literature.getLiterature();

    literature.deleteLiteratureEntry = function(entry) {
        var index = literature.literature.indexOf(entry);
        httpGetFactory('api/literature/delete/' + entry.id, function(response) {
            literature.literature.splice(index, 1);
        });
    };

    literature.editLiteratureEntry = function(entry) {
        var index = literature.literature.indexOf(entry);
        var entryCopy = angular.copy(entry);
        var typeName = entryCopy.type;
        delete entryCopy.type;
        var type;
        for(var i=0; i<literature.literatureOptions.availableTypes.length; i++) {
            var curr = literature.literatureOptions.availableTypes[i];
            if(curr.name == typeName) {
                type = curr;
                break;
            }
        }
        modalFactory.addLiteratureModal(literature.addLiterature, literature.literatureOptions.availableTypes, type, entryCopy, index);
    };

    literature.openAddLiteratureDialog = function() {
        modalFactory.addLiteratureModal(literature.addLiterature, literature.literatureOptions.availableTypes);
    };

    literature.addLiterature = function(fields, type, index) {
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
                formData.append(field, fields[field]);
            }
        }
        formData.append('type', type.name);
        httpPostFactory('api/literature/add', formData, function(lit) {
            if(lit.error) {
                alert(lit.error);
            } else {
                if(fields.id) {
                    literature.literature[index] = lit.literature[0];
                } else {
                    literature.literature.push(lit.literature[0]);
                    /*var container = document.getElementById('literature-container');
                    container.scrollTop = container.scrollTopMax;*/
                }
            }
        });
    };
    return literature;
}]);

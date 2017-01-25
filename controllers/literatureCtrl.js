spacialistApp.controller('literatureCtrl', function($rootScope, $scope, scopeService, modalService, httpGetFactory, httpPostFactory) {
    $scope.availableTypes = [
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
    $scope.literature.selectedType = $scope.availableTypes[0];

    $scope.addLiterature = function(fields, type) {
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
            formData.append(field, fields[field]);
        }
        formData.append('type', type.name);
        httpPostFactory('api/literature/add', formData, function(lit) {
            if(lit.error) {
                alert(lit.error);
            } else {
                scopeService.literature.push(lit.literature[0]);
                $scope.literature.push(lit.literature[0]);
            }
        });
    };
});

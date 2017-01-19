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
});

spacialistApp.component('root', {
    bindings: {
        user: '<',
        config: '<',
        userConfig: '<',
        concepts: '<'
    },
    template: '<ui-view/>'
});

spacialistApp.component('header', {
    bindings: {
        user: '<',
        concepts: '<',
        currentLanguage: '<',
        availableLanguages: '<'
    },
    templateUrl: 'templates/header.html',
    controller: 'langCtrl'
});

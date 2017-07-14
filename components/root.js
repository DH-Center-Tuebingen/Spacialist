spacialistApp.component('root', {
    bindings: {
        user: '<',
        config: '<',
        userConfig: '<',
        concepts: '<'
    },
    template: '<ui-view/>'
});

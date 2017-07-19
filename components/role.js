spacialistApp.component('role', {
    bindings: {
        roles: '<',
        permissions: '<',
        permissionsPerRole: '<',
        user: '<'
    },
    templateUrl: 'roles.html'
});

spacialistApp.component('roleedit', {
    bindings: {
        user: '<',
        role: '<'
    }
});

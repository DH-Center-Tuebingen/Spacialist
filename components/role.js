spacialistApp.component('role', {
    bindings: {
        roles: '<',
        permissions: '<',
        permissionsPerRole: '<',
        user: '<'
    },
    templateUrl: 'role.html',
    controller: 'userCtrl'
});

spacialistApp.component('user', {
    bindings: {
        user: '<',
        users: '<',
        roles: '<',
        rolesPerUser: '<'
    },
    templateUrl: 'user.html',
    controller: 'userCtrl'
});

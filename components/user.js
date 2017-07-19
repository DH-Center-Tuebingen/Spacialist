spacialistApp.component('user', {
    bindings: {
        user: '<',
        users: '<',
        roles: '<',
        rolesPerUser: '<'
    },
    templateUrl: 'user.html'
});

spacialistApp.component('useredit', {
    bindings: {
        user: '<',
        selectedUser: '<'
    }
});

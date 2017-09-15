spacialistApp.controller('userCtrl', ['$scope', 'userService', '$state', 'modalFactory', function($scope, userService, $state, modalFactory) {
    var localUsers = this.users;
    var localRoles = this.roles;

    $scope.loginError = userService.loginError;
    $scope.deleteUser = userService.deleteUser;

    $scope.loginUser = function(email, password) {
        var credentials = {
            email: email,
            password: password
        };
        userService.loginUser(credentials);
    };

    $scope.guestLogin = function() {
        var email = 'udontneedtoseehisidentification@rebels.tld';
        var pw = 'thesearentthedroidsuarelookingfor';
        $scope.loginUser(email, pw);
    };

    $scope.logoutUser = function() {
        userService.logoutUser();
    };

    $scope.deleteUser = function(user) {
        userService.deleteUser(user, localUsers);
    };

    $scope.addRolePermission = function(item, role) {
        userService.addRolePermission(item, role);
    };

    $scope.removeRolePermission = function(item, role) {
        userService.removeRolePermission(item, role);
    };

    $scope.deleteRole = function(role) {
        userService.deleteRole(role, localRoles);
    };

    $scope.openAddRoleDialog = function() {
        userService.openAddRoleDialog(localRoles);
    };

    $scope.addUserRole = function(item, user) {
        userService.addUserRole(item, user);
    };

    $scope.removeUserRole = function(item, user) {
        userService.removeUserRole(item, user);
    };

    $scope.openAddUserDialog = function() {
        userService.openAddUserDialog(localUsers);
    };
}]);

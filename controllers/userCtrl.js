spacialistApp.controller('userCtrl', ['$scope', 'userService', 'mainService', '$state', 'modalFactory', function($scope, userService, mainService, $state, modalFactory) {
    var localUsers = this.users;

    $scope.loginError = userService.loginError;
    $scope.deleteUser = userService.deleteUser;
    $scope.toggleEditMode = mainService.toggleEditMode;

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
        userService.deleteRole(role);
    };

    $scope.openAddRoleDialog = function() {
        userService.openEditRoleDialog();
    };

    $scope.openEditRoleDialog = function(role) {
        userService.openEditRoleDialog(role);
    };

    $scope.addUserRole = function(item, user_id) {
        userService.addUserRole(item, user_id);
    };

    $scope.removeUserRole = function(item, user_id) {
        userService.removeUserRole(item, user_id);
    };

    $scope.openAddUserDialog = function() {
        userService.openAddUserDialog(localUsers);
    };
}]);

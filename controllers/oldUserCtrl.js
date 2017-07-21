spacialistApp.controller('userCtrl', ['$scope', 'userService', 'mainService', 'analysisService', '$state', 'modalFactory', function($scope, userService, mainService, analysisService, $state, modalFactory) {
    $scope.currentUser = userService.currentUser;
    $scope.users = userService.users;
    $scope.roles = userService.roles;
    $scope.permissions = userService.permissions;
    $scope.loginError = userService.loginError;
    $scope.analysisEntries = analysisService.entries;
    $scope.setAnalysisEntry = analysisService.setAnalysisEntry;
    $scope.deleteUser = userService.deleteUser;
    $scope.toggleEditMode = mainService.toggleEditMode;

    $scope.openStartPage = function() {
        analysisService.unsetAnalysisEntry();
        $state.go('root.spacialist');
    };

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

    $scope.openUserManagement = function() {
        $state.go('root.user', {});
    };

    $scope.openRoleManagement = function() {
        $state.go('root.role', {});
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

    $scope.addUserRole = function($item, user_id) {
        userService.addUserRole($item, user_id);
    };

    $scope.removeUserRole = function($item, user_id) {
        userService.removeUserRole($item, user_id);
    };

    $scope.openAddUserDialog = function() {
        modalFactory.addUserModal(userService.addUser);
    };

    $scope.openEditUserDialog = function(user, $index) {
        var values = {
            id: user.id,
            name: user.name,
            email: user.email,
            password: ''
        };
        modalFactory.editUserModal(userService.editUser, values, $index);
    };
}]);

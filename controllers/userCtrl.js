spacialistApp.controller('userCtrl', ['$scope', 'scopeService', 'httpPostFactory', 'httpGetFactory', 'modalService', 'modalFactory', '$uibModal', '$auth', '$state', '$http', function($scope, scopeService, httpPostFactory, httpGetFactory, modalService, modalFactory, $uibModal, $auth, $state, $http) {
    scopeService.can = $scope.can = function(to) {
        if(typeof $scope.currentUser == 'undefined') return false;
        if(typeof $scope.currentUser.permissions[to] == 'undefined') return false;
        return $scope.currentUser.permissions[to] == 1;
    };

    $scope.openUserManagement = function() {
        $state.go('user', {});
    };

    $scope.openLiteratureView = function() {
        $state.go('literature', {});
    };

    $scope.getUserList = function() {
        httpPostFactory('api/user/get/all', new FormData(), function(response) {
            $scope.users = response.users;
        });
    };

    $scope.deleteUser = function(id, $index) {
        httpGetFactory('api/user/delete/' + id, function(response) {
            $scope.users.splice($index, 1);
        });
    };

    $scope.openAddUserDialog = function() {
        modalFactory.addUserModal(addUser);
    };

    var addUser = function(name, email, password) {
        var formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('password', password);
        httpPostFactory('api/user/add', formData, function(response) {
            $scope.users.push(response.user);
        });
    };

    $scope.openEditUserDialog = function(user, $index) {
        var values = {
            id: user.id,
            name: user.name,
            email: user.email,
            password: ''
        };
        modalFactory.editUserModal(editUser, values, $index);
    };

    var editUser = function(changes, id, $index) {
        var formData = new FormData();
        formData.append('user_id', id);
        for(var k in changes) {
            if(changes.hasOwnProperty(k)) {
                formData.append(k, changes[k]);
            }
        }
        httpPostFactory('api/user/edit', formData, function(response) {
            $scope.users[$index] = response.user;
        });
    };

    $scope.getRoles = function() {
        httpGetFactory('api/user/get/roles/all', function(response) {
            $scope.roles = response.roles;
        });
    };

    $scope.getUserRoles = function(id, $index) {
        httpGetFactory('api/user/get/roles/' + id, function(response) {
            $scope.users[$index].roles = response.roles;
        });
    };

    $scope.addUserRole = function($item, user_id) {
        var formData = new FormData();
        formData.append('role_id', $item.id);
        formData.append('user_id', user_id);
        httpPostFactory('api/user/add/role', formData, function(response) {
            // TODO only remove/add role if function returns no error
        });
    };

    $scope.removeUserRole = function($item, user_id) {
        var formData = new FormData();
        formData.append('role_id', $item.id);
        formData.append('user_id', user_id);
        httpPostFactory('api/user/remove/role', formData, function(response) {
            // TODO only remove/add role if function returns no error
        });
    };

    $scope.loginUser = function() {
        var credentials = {
            email: $scope.user.email,
            password: $scope.user.pw
        };
        $auth.login(credentials).then(function() {
            return $http.post('api/user/get');
        }, function(error) {
            console.log("error occured! " + error.data.error);
        }).then(function(response) {
            if(typeof response === 'undefined' || response.status !== 200) {
                $state.go('auth', {});
                return;
            }
            localStorage.setItem('user', JSON.stringify(response.data));
            scopeService.currentUser = $scope.currentUser = {
                user: response.data.user,
                permissions: response.data.permissions
            };
            console.log(JSON.stringify(response.data));
            $state.go('spacialist', {});
        });
    };

    $scope.guestLogin = function() {
        $scope.user.email = 'udontneedtoseehisidentification@rebels.tld';
        $scope.user.pw = 'thesearentthedroidsuarelookingfor';
        $scope.loginUser();
    };

    $scope.logoutUser = function() {
        $auth.logout().then(function() {
            console.log("logged out");
            $scope.user = {};
            localStorage.removeItem('user');
            scopeService.currentUser = $scope.currentUser = undefined;
            $state.go('auth', {});
        });
    };
}]);

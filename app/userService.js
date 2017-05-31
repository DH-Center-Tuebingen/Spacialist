spacialistApp.service('userService', ['httpPostFactory', 'httpGetFactory', 'modalFactory', 'snackbarService', '$auth', '$state', '$http', function(httpPostFactory, httpGetFactory, modalFactory, snackbarService, $auth, $state, $http) {
    var user = {};
    user.currentUser = {
        permissions: {},
        roles: {},
        user: {}
    };
    user.loginError = {};
    user.users = [];
    user.roles = [];
    user.permissions = [];
    user.can = function(to) {
        if(typeof user.currentUser == 'undefined') return false;
        if(typeof user.currentUser.permissions[to] == 'undefined') return false;
        return user.currentUser.permissions[to] == 1;
    };

    init();

    function init() {
        if(!userSet()) {
            $auth.logout().then(function() {
                user.currentUser.user = {};
                user.currentUser.permissions = {};
                localStorage.removeItem('user');
                $state.go('auth', {});
            });
        }
    }

    function userSet() {
        var user = localStorage.getItem('user');
        if(user === '') return false;
        var parsedUser = JSON.parse(user);
        if(!parsedUser) return false;
        return true;
    }

    user.getUserList = function() {
        user.users.length = 0;
        httpGetFactory('api/user', function(response) {
            angular.forEach(response.users, function(u, key) {
                user.users.push(u);
            });
        });
    };

    user.deleteUser = function(u) {
        console.log(u);
        httpDeleteFactory('api/user/' + u.id, function(response) {
            var index = user.users.indexOf(u);
            if(index > -1) user.users.splice(index, 1);
        });
    };

    user.addUser = function(name, email, password) {
        var formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('password', password);
        httpPostFactory('api/user', formData, function(response) {
            user.users.push(response.user);
        });
    };

    user.editUser = function(changes, id, $index) {
        var formData = new FormData();
        for(var k in changes) {
            if(changes.hasOwnProperty(k)) {
                formData.append(k, changes[k]);
            }
        }
        httpPatchFactory('api/user/' + id, formData, function(response) {
            user.users[$index] = response.user;
            var content = $translate.instant(snackbar.data-updated.success);
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.getRoles = function() {
        user.roles.length = 0;
        user.permissions.length = 0;
        httpGetFactory('api/user/role', function(response) {
            angular.forEach(response.permissions, function(perm) {
                user.permissions.push(perm);
            });
            angular.forEach(response.roles, function(role, key) {
                role.permissions = [];
                user.roles.push(role);
            });
        });
    };

    user.getRolePermissions = function(role) {
        if(role.permissions) role.permissions.length = 0;
        httpGetFactory('api/user/role/' + role.id + '/permission', function(response) {
            angular.forEach(response.permissions, function(perm) {
                role.permissions.push(perm);
            });
        });
    };

    user.openEditRoleDialog = function(role) {
        modalFactory.editRoleModal(editRole, role);
    };

    function editRole(role, changes) {
        var formData = new FormData();
        if(role) formData.append('role_id', role.id);
        for(var k in changes) {
            if(changes.hasOwnProperty(k)) {
                formData.append(k, changes[k]);
            }
        }
        httpPostFactory('api/role/edit', formData, function(response) {
            var isNew = false;
            if(!role) {
                role = {};
                isNew = true;
            }
            for(var k in response.role) {
                if(response.role.hasOwnProperty(k)) {
                    role[k] = response.role[k];
                }
            }
            if(isNew) {
                user.roles.push(role);
                var content = $translate.instant(snackbar.data-stored.success);
                snackbarService.addAutocloseSnack(content, 'success');
            } else {
                var content = $translate.instant(snackbar.data-updated.success);
                snackbarService.addAutocloseSnack(content, 'success');
            }
        });
    }

    user.deleteRole = function(role) {
        httpDeleteFactory('api/user/role/' + role.id, function(response) {
            if(response.error) return;
            var index = user.roles.indexOf(role);
            if(index > -1) user.roles.splice(index, 1);
        });
    };

    user.addRolePermission = function(item, role) {
        var formData = new FormData();
        formData.append('role_id', role.id);
        formData.append('permission_id', item.id);
        httpPostFactory('api/role/add/permission', formData, function(response) {
            // if an error occurs, remove added permission
            if(response.error) {
                var index = role.permissions.indexOf(item);
                if(index > -1) role.permissions.splice(index, 1);
                return;
            }
        });
    };

    user.removeRolePermission = function(item, role) {
        var formData = new FormData();
        formData.append('role_id', role.id);
        formData.append('permission_id', item.id);
        httpPostFactory('api/role/remove/permission', formData, function(response) {
            // if an error occurs, readd removed permission
            if(response.error) {
                role.permissions.push(item);
                var content = $translate.instant(snackbar.data-updated.error);
                snackbarService.addAutocloseSnack(content, 'error');
                return;
            }
            var content = $translate.instant(snackbar.data-updated.success);
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.getUserRoles = function(id, $index) {
        httpGetFactory('api/user/role/by_user/' + id, function(response) {
            user.users[$index].roles = response.roles;
        });
    };

    user.addUserRole = function($item, user_id) {
        var formData = new FormData();
        formData.append('role_id', $item.id);
        httpPutFactory('api/user/' + user_id + '/attachRole', formData, function(response) {
            // TODO only remove/add role if function returns no error
            var content = $translate.instant(snackbar.data-updated.success);
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.removeUserRole = function($item, user_id) {
        var formData = new FormData();
        formData.append('role_id', $item.id);
        httpPatchFactory('api/user/' + user_id + '/detachRole', formData, function(response) {
            // TODO only remove/add role if function returns no error
            var content = $translate.instant(snackbar.data-updated.success);
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.loginUser = function(credentials) {
        $auth.login(credentials).then(function() {
            return $http.get('api/user/active');
        }).then(function(response) {
            if(typeof response === 'undefined' || response.status !== 200) {
                $state.go('auth', {});
                return;
            }
            localStorage.setItem('user', JSON.stringify(response.data));
            user.currentUser.user = response.data.user;
            user.currentUser.permissions = response.data.permissions;
            console.log(JSON.stringify(response.data));
            delete user.loginError.message;
            $state.go('spacialist', {});
        });
    };

    user.logoutUser = function() {
        $auth.logout().then(function() {
            user.currentUser.user = {};
            user.currentUser.permissions = {};
            localStorage.removeItem('user');
            $state.go('auth', {});
        });
    };

    return user;
}]);

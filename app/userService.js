spacialistApp.service('userService', ['httpPostFactory', 'httpGetFactory', 'httpGetPromise', 'httpPutFactory', 'httpPatchFactory', 'httpPatchPromise', 'httpDeleteFactory', 'modalFactory', 'snackbarService', '$auth', '$state', '$http', '$translate', function(httpPostFactory, httpGetFactory, httpGetPromise, httpPutFactory, httpPatchFactory, httpPatchPromise, httpDeleteFactory, modalFactory, snackbarService, $auth, $state, $http, $translate) {
    var user = {};
    user.currentUser = {
        permissions: {},
        roles: {},
        user: {}
    };
    user.loginError = {};
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
                $state.go('login', {});
            });
        }
    }

    function userSet() {
        var user = getUser();
        if(user) return true;
        return false;
    }

    function getUser() {
        var user = localStorage.getItem('user');
        if(user !== '') {
            parsedUser = JSON.parse(user);
            if(parsedUser) {
                return parsedUser;
            }
        }
    }

    user.getUser = getUser;

    user.getUsers = function() {
        return httpGetPromise.getData('api/user').then(function(response) {
            return response.users;
        });
    };

    user.deleteUser = function(u, users) {
        httpDeleteFactory('api/user/' + u.id, function(response) {
            var index = users.indexOf(u);
            if(index > -1) users.splice(index, 1);
        });
    };

    user.openAddUserDialog = function(users) {
        modalFactory.addUserModal(user.addUser, users);
    };

    user.openAddRoleDialog = function(roles) {
        modalFactory.addRoleModal(user.addRole, roles);
    };

    user.addUser = function(name, email, password, users) {
        var formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('password', password);
        httpPostFactory('api/user', formData, function(response) {
            users.push(response.user);
        });
    };

    user.editUser = function(orgUser, newUser) {
        var oldValues = angular.copy(orgUser);
        var formData = new FormData();
        for(var k in newUser) {
            if(newUser.hasOwnProperty(k)) {
                if(newUser[k] != oldValues[k]) {
                    formData.append(k, newUser[k]);
                }
            }
        }
        return httpPatchPromise.getData('api/user/' + orgUser.id, formData).then(function(response) {
            for(var k in response.user) {
                if(response.user.hasOwnProperty(k)) {
                    orgUser[k] = response.user[k];
                }
            }
            var content = $translate.instant('snackbar.data-updated.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.getRoles = function() {
        return httpGetPromise.getData('api/user/role').then(function(response) {
            return response.roles;
        });
    };

    user.getPermissions = function() {
        return httpGetPromise.getData('api/user/permission').then(function(response) {
            return response.permissions;
        });
    };

    user.getRolePermissions = function(role) {
        httpGetFactory('api/user/role/' + role.id + '/permission', function(response) {
            role.permissions = response.permissions;
        });
    };

    user.addRole = function(role, roles) {
        if(!role.name) {
            var content = $translate.instant('snackbar.role.missing-name.error');
            snackbarService.addAutocloseSnack(content, 'error')
            return;
        }
        var formData = new FormData();
        for(var k in role) {
            if(role.hasOwnProperty(k)) {
                formData.append(k, role[k]);
            }
        }
        httpPostFactory('api/user/role', formData, function(response)  {
            var role = {};
            for(var k in response.role) {
                if(response.role.hasOwnProperty(k)) {
                    role[k] = response.role[k];
                }
            }
            roles.push(role);
            var content = $translate.instant('snackbar.data-stored.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.editRole = function(orgRole, newRole) {
        var oldValues = angular.copy(orgRole);
        var formData = new FormData();
        for(var k in newRole) {
            if(newRole.hasOwnProperty(k)) {
                if(newRole[k] != oldValues[k]) {
                    formData.append(k, newRole[k]);
                }
            }
        }
        return httpPatchPromise.getData('api/user/role/' + orgRole.name, formData).then(function(response) {
            for(var k in response.role) {
                if(response.role.hasOwnProperty(k)) {
                    orgRole[k] = response.role[k];
                }
            }
            var content = $translate.instant('snackbar.data-updated.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    function editRole(role, changes) {
        var formData = new FormData();
        for(var k in changes) {
            if(changes.hasOwnProperty(k)) {
                formData.append(k, changes[k]);
            }
        }
        if (role) {
            httpPatchFactory('api/user/role/'+role.name, formData, function(response) {
                for(var k in response.role) {
                    if(response.role.hasOwnProperty(k)) {
                        role[k] = response.role[k];
                    }
                }
                var content = $translate.instant('snackbar.data-updated.success');
                snackbarService.addAutocloseSnack(content, 'success');
            });
        } else {
            if (!changes.name) {
                snackbarService.addAutocloseSnack('Cannot create role without name', 'error')//TODO
                return;
            }
            httpPostFactory('api/user/role', formData, function(response)  {
                var role = {};
                for(var k in response.role) {
                    if(response.role.hasOwnProperty(k)) {
                        role[k] = response.role[k];
                    }
                }
                user.roles.push(role);
                var content = $translate.instant('snackbar.data-stored.success');
                snackbarService.addAutocloseSnack(content, 'success');
            });

        }
    }

    user.deleteRole = function(role, roles) {
        httpDeleteFactory('api/user/role/' + role.id, function(response) {
            if(response.error) return;
            var index = roles.indexOf(role);
            if(index > -1) roles.splice(index, 1);
        });
    };

    user.addRolePermission = function(item, role) {
        httpPutFactory('api/user/permission_role/' + role.id + '/' + item.id, new FormData(), function(response) {
            // if an error occurs, remove added permission
            if(response.error) {
                var index = role.permissions.indexOf(item);
                if(index > -1) role.permissions.splice(index, 1);
                var content = $translate.instant('snackbar.data-updated.error');
                snackbarService.addAutocloseSnack(content, 'error');
                return;
            }
            var content = $translate.instant('snackbar.data-updated.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.removeRolePermission = function(item, role) {
        httpDeleteFactory('api/user/permission_role/'+role.id+'/'+item.id, function(response) {
            // if an error occurs, readd removed permission
            if(response.error) {
                role.permissions.push(item);
                var content = $translate.instant('snackbar.data-updated.error');
                snackbarService.addAutocloseSnack(content, 'error');
                return;
            }
            var content = $translate.instant('snackbar.data-updated.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.getUserRoles = function(user) {
        httpGetFactory('api/user/role/by_user/' + user.id, function(response) {
            user.roles = response.roles;
        });
    };

    user.addUserRole = function(item, user) {
        var formData = new FormData();
        formData.append('role_id', item.id);
        httpPatchFactory('api/user/' + user.id + '/attachRole', formData, function(response) {
            // if an error occurs, remove added role
            if(response.error) {
                var index = user.roles.indexOf(item);
                if(index > -1) user.roles.splice(index, 1);
                var content = $translate.instant('snackbar.data-updated.error');
                snackbarService.addAutocloseSnack(content, 'error');
                return;
            }
            var content = $translate.instant('snackbar.data-updated.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.removeUserRole = function(item, user) {
        var formData = new FormData();
        formData.append('role_id', item.id);
        httpPatchFactory('api/user/' + user_id + '/detachRole', formData, function(response) {
            // if an error occurs, readd removed role
            if(response.error) {
                user.roles.push(item);
                var content = $translate.instant('snackbar.data-updated.error');
                snackbarService.addAutocloseSnack(content, 'error');
                return;
            }
            var content = $translate.instant('snackbar.data-updated.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };

    user.loginUser = function(credentials) {
        $auth.login(credentials).then(function() {
            return $http.get('api/user/active');
        }).then(function(response) {
            if(typeof response === 'undefined' || response.status !== 200) {
                $state.go('login', $state.params);
                return;
            }
            localStorage.setItem('user', JSON.stringify(response.data));
            user.currentUser.user = response.data.user;
            user.currentUser.permissions = response.data.permissions;
            delete user.loginError.message;
            $state.go($state.params.toState, $state.params.toParams); // TODO should redirect to old state
        });
    };

    user.logoutUser = function() {
        $auth.logout().then(function() {
            user.currentUser.user = {};
            user.currentUser.permissions = {};
            localStorage.removeItem('user');
            $state.go('login', {});
        });
    };

    return user;
}]);

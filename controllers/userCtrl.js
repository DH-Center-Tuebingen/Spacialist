spacialistApp.controller('userCtrl', ['$scope', 'scopeService', 'httpPostFactory', 'httpGetFactory', 'modalService', '$uibModal', '$auth', '$state', '$http', function($scope, scopeService, httpPostFactory, httpGetFactory, modalService, $uibModal, $auth, $state, $http) {
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

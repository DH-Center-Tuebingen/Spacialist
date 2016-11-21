var spacialistApp = angular.module('tutorialApp', ['ngAnimate', 'satellizer', 'ui.router', 'ngRoute', 'ngMessages', 'ui-leaflet', 'ui.select', 'ngSanitize', 'pascalprecht.translate', 'ngFlag', 'ui.bootstrap', 'monospaced.mousewheel', 'ngFileUpload', 'ui.tree', 'infinite-scroll', 'ui.bootstrap.contextMenu']);

spacialistApp.service('modalService', ['$uibModal', function($uibModal) {
    var defaults = {
        backdrop: true,
        keyboard: true,
        modalFade: true,
        templateUrl: 'layouts/modal-popup.html',
        windowClass: 'wide-modal'
    };
    var options = {};

    this.showModal = function(customDefaults, customOptions) {
        if(!customDefaults) customDefaults = {};
        //customDefaults.backdrop = 'static';
        return this.show(customDefaults, customOptions);
    };

    this.show = function(customDefaults, customOptions) {
        var tempDefaults = {};
        var tempOptions = {};

        angular.extend(tempDefaults, defaults, customDefaults);
        angular.extend(tempOptions, options, customOptions);
        tempOptions.modalNav = {
            propTab: true,
            linkTab: false,
            setPropTab: function() {
                tempOptions.modalNav.propTab = true;
                tempOptions.modalNav.linkTab = false;
            },
            setLinkTab: function() {
                tempOptions.modalNav.propTab = false;
                tempOptions.modalNav.linkTab = true;
            }
        }

        if(!tempDefaults.controller) {
            tempDefaults.controller = function($scope, $uibModalInstance) {
                $scope.modalOptions = tempOptions;
                var initHeight = -1;
                var initWidth = -1;
                var dragging = false;
                var startX = -1;
                var startY = -1;
                var startTop = 0;
                var startLeft = 0;
                $scope.modalOptions.close = function(result) {
                    $uibModalInstance.dismiss('cancel');
                }
                $scope.modalOptions.mDown = function(event) {
                    var img = document.querySelector('#modalImage');
                    img.style.cursor = "grabbing";
                    dragging = true;
                    startX = event.clientX;
                    startY = event.clientY;
                    startTop = img.offsetTop;
                    startLeft = img.offsetLeft;
                }
                $scope.modalOptions.mUp = function(event) {
                    var img = document.querySelector('#modalImage');
                    dragging = false;
                    img.style.cursor = "grab";
                }
                $scope.modalOptions.mLeave = function(event) {
                    var img = document.querySelector('#modalImage');
                    dragging = false;
                    img.style.cursor = "grab";
                }
                $scope.modalOptions.mMove = function(event) {
                    if(dragging) {
                        var img = document.querySelector('#modalImage');
                        var div = document.querySelector('#imageWrapper');
                        var newTop = startTop + (event.clientY - startY);
                        var newLeft = startLeft + (event.clientX - startX);
                        var minTop, maxTop, minLeft, maxLeft;
                        if(img.height >= div.offsetHeight) {
                            minTop = -img.height + div.offsetHeight;
                            maxTop = 0;
                        } else {
                            minTop = 0;
                            maxTop = div.offsetHeight - img.height;
                        }
                        if(img.width >= div.offsetWidth) {
                            minLeft = -img.width + div.offsetWidth;
                            maxLeft = 0;
                        } else {
                            minLeft = 0;
                            maxLeft = div.offsetWidth - img.width;
                        }

                        if(newTop < minTop) newTop = minTop;
                        else if(newTop > maxTop) newTop = maxTop;
                        if(newLeft < minLeft) newLeft = minLeft;
                        else if(newLeft > maxLeft) newLeft = maxLeft;
                        img.style.top = newTop + "px";
                        img.style.left = newLeft + "px";
                    }
                }
                $scope.modalOptions.mScroll = function(event, d, dx, dy) {
                    var slider = document.querySelector('#width-25');
                    var newZoom = parseInt($scope.modalOptions.zoomlevel, 10) + (slider.step * dy);
                    if(newZoom < slider.min) newZoom = parseInt(slider.min, 10);
                    if(newZoom > slider.max) newZoom = parseInt(slider.max, 10);
                    $scope.modalOptions.zoomlevel = newZoom;
                    $scope.modalOptions.zoomIn();
                }
                $scope.modalOptions.zoomIn = function() {
                    var img = document.querySelector('#modalImage');
                    var zl = parseInt($scope.modalOptions.zoomlevel) / 100.0;
                    if(initHeight == -1) initHeight = img.height;
                    if(initWidth == -1) initWidth = img.width;
                    img.height = initHeight * zl;
                    img.width = initWidth * zl;
                }
            }
        }
        var modalInstance = $uibModal.open(tempDefaults);
        modalInstance.result.then(function(selectedItem) {
        }, function() {
        });
        return modalInstance;
    }
}]);

spacialistApp.service('modalFactory', ['$uibModal', function($uibModal) {
    this.deleteModal = function(elementName, onConfirm, additionalWarning) {
        if(typeof additionalWarning != 'undefined' && additionalWarning !== '') {
            var warning = additionalWarning;
        }
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/delete-confirm.html',
            controller: function($uibModalInstance) {
                this.name = elementName;
                this.warning = warning;
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.deleteConfirmed = function() {
                    onConfirm();
                    $uibModalInstance.dismiss('ok');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };
    this.createModal = function(heading, text, selection, onCreate) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/create-element.html',
            controller: function($uibModalInstance) {
                this.heading = heading;
                this.desc = text;
                this.choices = selection;
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.onCreate = function(name, type) {
                    onCreate(name, type);
                    $uibModalInstance.dismiss('ok');
                };
                this.setSelected = function(ngModel) {
                    this.type = ngModel.$modelValue;
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };
}]);

spacialistApp.directive('mySetIndex', function() {
    return {
        link: function(scope, element, attr) {
            if(typeof attr.new !== 'undefined') scope.attribDataType = 'new';
            else if(typeof attr.edit !== 'undefined') scope.attribDataType = 'edit';
        },
        templateUrl: 'includes/varFields.html'
    }
});

spacialistApp.directive('spinner', function() {
    return {
        template: '<div class="spinner-container">\
            <svg class="circle-img-path" viewBox="25 25 50 50">\
                <circle class="circle-path" cx="50" cy="50" r="20" fill="none" stroke-width="4" stroke-miterlimit="10" />\
            </svg>\
        </div>'
    }
});

spacialistApp.directive('myDirective', function(httpPostFactory, scopeService) {
    return {
        restrict: 'A',
        scope: false,
        link: function(scope, element, attr) {
            element.bind('change', function() {
                var formData = new FormData();
                formData.append('file', element[0].files[0]);
            });
        }
    }
});

spacialistApp.directive('myTree', function($parse) {
    return {
        restrict: 'E',
        templateUrl: 'includes/new-tree.html',
        scope: {
            onClickCallback: '&',
            onInit: '&',
            itemList: '=',
            element: '=',
            displayAttribute: '=',
            typeAttribute: '=',
            prefixAttribute: '=',
            setContextMenu: '='
        },
        controller: 'mainCtrl'
    };
});

spacialistApp.directive('formField', function() {
    var updateInputFields = function(scope, element, attrs) {
        scope.attributeFields = scope.$eval(attrs.fields);
        scope.attributeOutputs = scope.$eval(attrs.output);
        var pattern = /^\d+$/;
        if(typeof attrs.labelWidth != 'undefined' && pattern.test(attrs.labelWidth)) {
            scope.labelWidth = parseInt(attrs.labelWidth);
        } else {
            scope.labelWidth = 4;
        }
        if(typeof attrs.inputWidth != 'undefined' && pattern.test(attrs.inputWidth)) {
            scope.inputWidth = parseInt(attrs.inputWidth);
        } else {
            scope.inputWidth = 8;
        }
        if(typeof attrs.offset != 'undefined' && pattern.test(attrs.offset)) {
            scope.offset = parseInt(attrs.offset);
        } else {
            scope.offset = 0;
        }
        if(scope.labelWidth + scope.inputWidth + scope.offset > 12) {
            console.log("> 12");
            return false;
        }
    }

    return {
        restrict: 'E',
        templateUrl: 'includes/inputFields.html',
        scope: false,
        link: function(scope, element, attrs) {
            scope.listInput = {};
            scope.$watch(function(scope) {
                return scope.$eval(attrs.fields);
            }, function(newVal, oldVal) {
                updateInputFields(scope, element, attrs);
            });
            scope.$watch(function(scope) {
                return scope.$eval(attrs.output);
            }, function(newVal, oldVal) {
                updateInputFields(scope, element, attrs);
            });
        }
    }
});

spacialistApp.directive("number", function() {
    return {
        restrict: "A",
        require: "ngModel",
        link: function(scope, element, attributes, ngModel) {
            ngModel.$validators.number = function(modelValue) {
                return isFinite(modelValue);
            }
        }
    };
});

spacialistApp.filter('urlify', function() {
    var urls = /(\b(https?|ftp):\/\/[A-Z0-9+&@#\/%?=~_|!:,.;-]*[-A-Z0-9+&@#\/%=~_|])/gim;
    return function(text) {
        if(text.match(urls)) {
            text = text.replace(urls, '<a href="$1" target="_blank">$1</a>');
        }
        return text;
    }
})

spacialistApp.filter('dateBcAc', ['$q', '$translate', function($q, $translate) {
    var bcStr = null;
    var adStr = null;
    var translated = false;

    function appendDate(date) {
        if(date < 0) {
            return Math.abs(date) + " " + bcStr;
        } else {
            return date + " " + adStr;
        }
    }

    filterStub.$stateful = true;
    function filterStub(date) {
        if(bcStr === null || adStr === null) {
            if(!translated) {
                translated = true;
                $translate('bc').then(function(bc) {
                    bcStr = bc;
                });
                $translate('ad').then(function(ad) {
                    adStr = ad;
                });
            }
            return date;
        } else {
            return appendDate(date);
        }
    }
    return filterStub;
}]);

spacialistApp.filter('bytes', function() {
	return function(bytes, precision) {
        var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		if(isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '0 ' + units[0];
		if(typeof precision === 'undefined') precision = 1;
		var number = Math.floor(Math.log(bytes) / Math.log(1024));
		return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) +  ' ' + units[number];
	}
});

spacialistApp.filter('overallLength', function() {
    return function(obj) {
        var count = 0;
        angular.forEach(obj, function(value, key) {
            count += value.length;
        });
        return count;
    }
});

spacialistApp.filter('filterByMarkerName', function() {
    return function(markers, search) {
        var searchPattern = new RegExp(search, "i");
        var tempMarkers = angular.extend({}, markers);
        angular.forEach(tempMarkers, function(mV, mK) {
            if(!searchPattern.test(mV.myOptions.name)) {
                delete tempMarkers[mK];
            }
        });
        return tempMarkers;
    }
});

spacialistApp.filter('filterUnlinkedMarker', function() {
    return function(markers, linkIds) {
        if(linkIds.length == 0) return {};
        var tempIds = linkIds.slice();
        var tempMarkers = angular.extend({}, markers);
        var linkedMarkers = {};
        angular.forEach(tempMarkers, function(mV, mK) {
            for(var i=0; i<tempIds.length; i++) {
                var lV = tempIds[i];
                if(lV == mV.id) {
                    linkedMarkers[mK] = mV;
                    delete tempMarkers[mK];
                    tempIds.splice(i, 1);
                    break;
                }
            }
        });
        return linkedMarkers;
    }
});

spacialistApp.filter('filterLinkedMarker', function() {
    return function(markers, linkIds) {
        if(linkIds.length == 0) return markers;
        var tempIds = linkIds.slice();
        var tempMarkers = angular.extend({}, markers);
        angular.forEach(tempMarkers, function(mV, mK) {
            for(var i=0; i<tempIds.length; i++) {
                var lV = tempIds[i];
                if(lV == mV.id) {
                    delete tempMarkers[mK];
                    tempIds.splice(i, 1);
                    break;
                }
            }
        });
        return tempMarkers;
    }
});

spacialistApp.filter('linkedFilter', function() {
    return function(imgs, linked, unlinked) {
        var filteredImgs = [];
        if(typeof linked === 'undefined') linked = false;
        if(typeof unlinked === 'undefined') unlinked = false;
        if(linked && unlinked) {
            return filteredImgs;
        }
        if(!linked && !unlinked) {
            return imgs;
        }
        angular.forEach(imgs, function(value, key) {
            if(linked && typeof value.linked !== 'undefined' && value.linked > 0) {
                filteredImgs.push(value);
            } else if(unlinked && (typeof value.linked === 'undefined' || value.linked <= 0)) {
                filteredImgs.push(value);
            }
        });
        return filteredImgs;
    }
});

spacialistApp.filter('contextFilter', function() {
    return function(imgs, contexts) {
        //TODO implement contexts (beside libraries)
        return imgs;
    }
});

spacialistApp.filter('truncate', function () {
    return function (value, max, atword, suffix) {
        if(!value) return '';
        if(!max || value.length <= max) return value;

        value = value.substr(0, max);
        if(atword) {
            var lastWordIndex = value.lastIndexOf(' ');
            if(lastWordIndex != -1) {
                if(value.endsWith(',', lastWordIndex) || value.endsWith('.', lastWordIndex)) lastWordIndex--;
                value = value.substr(0, lastWordIndex);
            }
        }
        return value + (suffix || '…');
    };
});

spacialistApp.factory('httpPostPromise', function($http) {
    var getData = function(file, data) {
        return $http({
            url: file,
            method: "POST",
            data: data,
            headers: {
                'Content-Type': undefined
            }
        }).then(function(result) {
            return result.data;
        });
    };
    return { getData: getData };
});

spacialistApp.factory('httpPostFactory', function($http) {
    return function(file, data, callback) {
        $http({
            url: file,
            method: "POST",
            data: data,
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response) {
            callback(response);
        });
    };
});

spacialistApp.factory('httpGetPromise', function($http) {
    var getData = function(file) {
        return $http({
            url: file,
            method: "GET",
            headers: {
                'Content-Type': undefined
            }
        }).then(function(result) {
            return result.data;
        });
    };
    return { getData: getData };
});

spacialistApp.factory('httpGetFactory', function($http) {
    return function(file, callback) {
        $http({
            url: file,
            method: "GET",
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response) {
            callback(response);
        });
    };
});

spacialistApp.factory('scopeService', function($http) {
    var roles = [
        "admin",
        "student_assistent"
    ]
    var service = {
        map: undefined,
        datetable: "",
        epochs: [],
        filedesc: undefined,
        markers: {},
        center: {},
        events: {},
        user: {
            roles: roles,
            activeRole: roles[0]
        },
        drawOptions: {},
        ctxts: []
    };
    return service;
});

spacialistApp.config(function($translateProvider) {
    $translateProvider.useStaticFilesLoader({
        prefix: 'l10n/',
        suffix: '.json'
    });
    $translateProvider.registerAvailableLanguageKeys(['en', 'de', 'fr', 'it', 'es'], {
        'de_DE': 'de',
        'de_AT': 'de',
        'de_CH': 'de',
        'en_UK': 'en',
        'en_US': 'en'
    });
    $translateProvider.determinePreferredLanguage();
    $translateProvider.useSanitizeValueStrategy('escape');
});

spacialistApp.config(function($controllerProvider, $provide) {
    $provide.factory('moduleHelper', function() {
        return {
            controllerExists: function(name) {
                return $controllerProvider.has(name);
            }
        };
    });
});

spacialistApp.config(function($stateProvider, $urlRouterProvider, $authProvider, $httpProvider, $provide) {
    function redirectWhenLoggedOut($q, $injector) {
        return {
            responseError: function(rejection) {
                if(typeof rejection != 'undefined') {
                    var rejectReasons = ['token_not_provided', 'token_expired', 'token_absent', 'token_invalid'];
                    angular.forEach(rejectReasons, function(value, key) {
                        if(rejection.data.error === value) {
                            var $state = $injector.get('$state');
                            localStorage.removeItem('user');
                            $state.go('auth');
                        }
                    });
                }
                return $q.reject(rejection);
            }
        };
    }
    $provide.factory('redirectWhenLoggedOut', redirectWhenLoggedOut);

    function redirectWhenUnauth($q, $injector) {
        return {
            'response': function(response) {
                return response || $q.when(response);
            },
            'responseError': function(rejection) {
                if(rejection.status == 401) {
                    var $state = $injector.get('$state');
                    localStorage.removeItem('user');
                    $state.go('auth');
                    return;
                }
                return $q.reject(rejection);
            }
        };
    }
    $provide.factory('redirectWhenUnauth', redirectWhenUnauth);

    $httpProvider.interceptors.push('redirectWhenLoggedOut');
    $httpProvider.interceptors.push('redirectWhenUnauth');

    $authProvider.loginUrl = '../spacialist_api/user/login';
    $urlRouterProvider.otherwise('/auth');

    $stateProvider
        .state('auth', {
            url: '/auth',
            templateUrl: 'layouts/login.html',
            controller: 'userCtrl'
        })
        .state('spacialist', {
            url: '/spacialist',
            //template: '<h4>Hallo {{ currentUser.user }} <small>{{ currentUser.user.email }}</small></h4>',
            templateUrl: 'map.html',
            controller: 'mapCtrl'
        })
        .state('testing', {
            url: '/testing',
            templateUrl: 'testing.html',
            controller: 'mainCtrl'
        });
});

/**
 * Redirect user to 'spacialist' state if they are already logged in and access the 'auth' state
 */
spacialistApp.run(function($rootScope, $state) {
    $rootScope.$on('$stateChangeStart', function(event, toState) {
        var user = JSON.parse(localStorage.getItem('user'));
        if(user) {
            $rootScope.currentUser = {
                user: user
            }
            if(toState.name === 'auth') {
                event.preventDefault();
                $state.go('spacialist');
            }
        }
    });
});

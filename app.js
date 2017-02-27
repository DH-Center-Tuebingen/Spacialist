var spacialistApp = angular.module('tutorialApp', ['ngAnimate', 'satellizer', 'ui.router', 'ngRoute', 'ngMessages', 'ui-leaflet', 'ui.select', 'ngSanitize', 'pascalprecht.translate', 'ngFlag', 'ui.bootstrap', 'ngFileUpload', 'ui.tree', 'infinite-scroll', 'ui.bootstrap.contextMenu']);

spacialistApp.service('modalService', ['$uibModal', 'httpGetFactory', function($uibModal, httpGetFactory) {
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
        };

        if(!tempDefaults.controller) {
            tempDefaults.controller = function($scope, $uibModalInstance) {
                $scope.modalOptions = tempOptions;
                $scope.modalOptions.close = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                $scope.modalOptions.openImageInTab = function(id) {
                    httpGetFactory('api/image/get/' + id, function(data) {
                        window.open(data);
                    });
                };
            };
        }
        var modalInstance = $uibModal.open(tempDefaults);
        modalInstance.result.then(function(selectedItem) {
        }, function() {
        });
        return modalInstance;
    };
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
    this.addUserModal = function(onCreate) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/new-user.html',
            controller: function($uibModalInstance) {
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.onCreate = function(name, email, password) {
                    onCreate(name, email, password);
                    $uibModalInstance.dismiss('ok');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function() {}, function() {});
    };
    this.editUserModal = function(onEdit, user, index) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/edit-user.html',
            controller: function($uibModalInstance) {
                this.userinfo = angular.copy(user);
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.onEdit = function(userinfo) {
                    var changes = {};
                    for(var key in user) {
                        if(user.hasOwnProperty(key)) {
                            if(user[key] != userinfo[key]) {
                                changes[key] = userinfo[key];
                            }
                        }
                    }
                    onEdit(changes, user.id, index);
                    $uibModalInstance.dismiss('ok');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function() {}, function() {});
    };
    this.addLiteratureModal = function(onCreate, types, selectedType, fields, index) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/new-literature.html',
            controller: function($uibModalInstance) {
                this.availableTypes = types;
                this.selectedType = selectedType || types[0];
                this.fields = fields;
                this.index = index;
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.onCreate = function(fields, type) {
                    onCreate(fields, type, index);
                    $uibModalInstance.dismiss('ok');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function() {}, function() {});
    };
    this.errorModal = function(msg) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/error.html',
            controller: function($uibModalInstance) {
                this.msg = msg;
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };
}]);

spacialistApp.directive('spinner', function() {
    return {
        template: '<div class="spinner-container">' +
            '<svg class="circle-img-path" viewBox="25 25 50 50">' +
                '<circle class="circle-path" cx="50" cy="50" r="20" fill="none" stroke-width="4" stroke-miterlimit="10" />' +
            '</svg>' +
        '</div>'
    };
});

spacialistApp.directive('resizeWatcher', function($window) {
    return function(scope, element) {
        var headerPadding = 20;
        var bottomPadding = 20;

        scope.getViewportDim = function() {
            return {
                'height': $window.innerHeight,
                'width': $window.innerWidth,
                'isSm': window.matchMedia("(max-width: 991px)").matches
            };
        };
        scope.$watch(scope.getViewportDim, function(newValue, oldValue) {
            if(newValue.isSm) {
                $('#tree-container').css('height', '');
                $('#attribute-container').css('height', '');
                $('#addon-container').css('height', '');
                $('#literature-container').css('height', '');
                $('analysis-frame').css('height', '');
            } else {
                var height = newValue.height;
                var width = newValue.width;

                var headerHeight = document.getElementById('header-nav').offsetHeight;
                var addonNavHeight = 0;
                var addonNav = document.getElementById('addon-nav');
                if(addonNav) addonNavHeight = addonNav.offsetHeight;
                var containerHeight = scope.containerHeight = height - headerHeight - headerPadding - bottomPadding;
                var addonContainerHeight = scope.addonContainerHeight = containerHeight - addonNavHeight;
                var literatureContainer = document.getElementById('literature-container');
                if(literatureContainer) {
                    var literatureHeight = containerHeight;
                    var literatureAddButton = document.getElementById('literature-add-button');
                    if(literatureAddButton) literatureHeight -= literatureAddButton.offsetHeight;
                    var literatureSearch = document.getElementById('literature-search-form');
                    if(literatureSearch) literatureHeight -= literatureSearch.offsetHeight;
                    var literatureTable = document.getElementById('literature-table');
                    if(literatureTable) {
                        var head = literatureTable.tHead;
                        angular.element(literatureContainer).bind('scroll', function(e) {
                            var t = 'translate(0, ' + this.scrollTop + 'px)';
                            head.style.transform = t;
                        });
                        var headHeight = head.offsetHeight;
                        var body = literatureTable.tBodies[0];
                        $(body).css('max-height', literatureHeight - headHeight);
                        $(literatureContainer).css('height', literatureHeight);
                    }
                }

                $('#tree-container').css('height', containerHeight);
                $('#attribute-container').css('height', containerHeight);
                $('#addon-container').css('height', containerHeight);
                $('#analysis-frame').css('height', containerHeight);
            }
        }, true);
    };
});

spacialistApp.directive('myDirective', function(httpPostFactory) {
    return {
        restrict: 'A',
        scope: false,
        link: function(scope, element, attr) {
            element.bind('change', function() {
                var formData = new FormData();
                formData.append('file', element[0].files[0]);
            });
        }
    };
});

spacialistApp.directive('myTree', function($parse) {
    return {
        restrict: 'E',
        templateUrl: 'includes/new-tree.html',
        scope: {
            onClickCallback: '&',
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

spacialistApp.directive('imageList', function() {
    return {
        restrict: 'E',
        templateUrl: 'includes/image-list.html',
        scope: {
            onScrollLoad: '&',
            scrollContainer: '=',
            imageData: '=',
            imageType: '='
        },
        controller: 'imageCtrl',
        link: function(scope, elements, attrs) {
            scope.$root.$on('image:delete:linked', function(event, args) {
                scope.tmpData.linked = [];
            });
        }
    };
});

spacialistApp.directive('formField', function() {
    var updateInputFields = function(scope, element, attrs) {
        scope.attributeFields = scope.$eval(attrs.fields);
        scope.attributeOutputs = scope.$eval(attrs.output);
        scope.attributeSources = scope.$eval(attrs.sources);
        scope.readonlyInput = scope.$eval(attrs.spReadonly);
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
    };

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
            scope.$watch(function(scope) {
                return scope.$eval(attrs.sources);
            }, function(newVal, oldVal) {
                updateInputFields(scope, element, attrs);
            });
        }
    };
});

spacialistApp.directive('protectedSrc', ['httpGetFactory', function(httpGetFactory) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            httpGetFactory(attrs.protectedSrc, function(response) {
                attrs.$set('src', response);
            });
        }
    };
}]);

spacialistApp.directive("number", function() {
    return {
        restrict: "A",
        require: "ngModel",
        link: function(scope, element, attributes, ngModel) {
            ngModel.$validators.number = function(modelValue) {
                return isFinite(modelValue);
            };
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
    };
});

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
	};
});

spacialistApp.filter('overallLength', function() {
    return function(obj) {
        var count = 0;
        angular.forEach(obj, function(value, key) {
            count += value.length;
        });
        return count;
    };
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
    };
});

spacialistApp.filter('filterUnlinkedMarker', function() {
    return function(markers, linkIds) {
        if(linkIds.length === 0) return {};
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
    };
});

spacialistApp.filter('filterLinkedMarker', function() {
    return function(markers, linkIds) {
        if(linkIds.length === 0) return markers;
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
    };
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
    };
});

spacialistApp.filter('contextFilter', function() {
    return function(imgs, contexts) {
        //TODO implement contexts (beside libraries)
        return imgs;
    };
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
    var lastError;
    var rejectReasons = [
        'user_not_found',
        'token_not_provided',
        'token_expired',
        'token_absent',
        'token_invalid'
    ];
    var rejectTranslations = [
        'Invalid username or password',
        'Authentication error (token not provided)',
        'Session is expired. Please login again.',
        'Authentication error (token absent)',
        'Authentication error (invalid token)'
    ];

    function updateToken(response, $injector) {
        if(response.headers('Authorization') !== null) {
            var token = response.headers('Authorization').replace('Bearer ', '');
            var $auth = $injector.get('$auth');
            $auth.setToken(token);
            localStorage.setItem('satellizer_token', token);
        }
    }

    function setAuthHeader($q, $injector) {
        return {
            response: function(response) {
                updateToken(response, $injector);
                return response || $q.when(response);
            },
            responseError: function(rejection) {
                console.log("Something went wrong...");
                var reasonIndex = rejectReasons.indexOf(rejection.data.error);
                if(rejection.data && reasonIndex > -1) {
                    localStorage.removeItem('user');
                    var userService = $injector.get('userService');
                    userService.loginError.message = rejectTranslations[reasonIndex];
                } else if(rejection.status == 400 || rejection.status == 401) {
                    var $state = $injector.get('$state');
                    var userService = $injector.get('userService');
                    userService.loginError.message = 'An error occured. Please log in again';
                    $state.go('auth');
                } else {
                    updateToken(rejection, $injector);
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(rejection.data, "text/xml");
                    var errors = doc.getElementsByClassName('exception_message');
                    var modalFactory = $injector.get('modalFactory');
                    var errorMsg;
                    if(typeof errors[0] != 'undefined' && errors[0].innerHTML) {
                        errorMsg = errors[0].innerHTML;
                    } else {
                        errorMsg = rejection.statusText;
                    }
                    if(!lastError || lastError != rejection.status) {
                        lastError = rejection.status;
                        modalFactory.errorModal(errorMsg);
                    }
                }
                return $q.reject(rejection);
            }
        };
    }
    $provide.factory('setAuthHeader', setAuthHeader);

    $httpProvider.interceptors.push('setAuthHeader');

	$authProvider.baseUrl = '.';
    $authProvider.loginUrl = 'api/user/login';
    $urlRouterProvider.otherwise('/auth');

    $stateProvider
        .state('auth', {
            url: '/auth',
            templateUrl: 'layouts/login.html',
            controller: 'userCtrl'
        })
        .state('spacialist', {
            url: '/spacialist',
            templateUrl: 'view.html'
        })
        .state('user', {
            url: '/user',
            templateUrl: 'user.html'
        })
        .state('literature', {
            url: '/literature',
            templateUrl: 'literature.html'
        });
});

/**
 * Redirect user to 'spacialist' state if they are already logged in and access the 'auth' state
 */
spacialistApp.run(function($rootScope, $state, mapService, userService) {
    $rootScope.$on('$stateChangeStart', function(event, toState) {
        var user = localStorage.getItem('user');
        if(user !== '') {
            parsedUser = JSON.parse(user);
            if(parsedUser) {
                userService.currentUser.user = parsedUser.user;
                userService.currentUser.permissions = parsedUser.permissions;
                if(!userService.can('duplicate_edit_concepts')) {
                    if(typeof mapService.map != 'undefined') {
                        mapService.map.drawOptions.draw = {
                            polyline: false,
                            polygon: false,
                            rectangle: false,
                            circle: false,
                            marker: false
                        };
                    }
                }
                if(toState.name === 'auth') {
                    event.preventDefault();
                    $state.go('spacialist');
                }
            }
        }
    });
});

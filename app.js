var spacialistApp = angular.module('tutorialApp', ['ngAnimate', 'satellizer', 'ui.router', 'ngRoute', 'ngMessages', 'ngCookies', 'ui-leaflet', 'ui.select', 'ngSanitize', 'pascalprecht.translate', 'ngFlag', 'hljs', 'hc.marked', 'pdf', 'ui.bootstrap', 'ngFileUpload', 'ui.tree', 'infinite-scroll', 'ui.bootstrap.contextMenu']);

$.material.init();

spacialistApp.service('searchService', ['$translate', function($translate) {
    var search = {};

    search.formatUnixDate = function(ts) {
        var d = new Date(ts);
        return d.toLocaleDateString($translate.use());
    };

    search.availableSearchTerms = {
        tags: [],
        dates: [],
        cameras: []
    };

    return search;
}]);

spacialistApp.service('snackbarService', [function() {
    var defaults = {
        autoclose: {
            timeout: 2000,
            htmlAllowed: true
        },
        persistent: {
            timeout: 0,
            htmlAllowed: true
        }
    };

    function getAutocloseSnack() {
        return angular.merge({}, defaults.autoclose);
    }
    function getPersistentSnack() {
        return angular.merge({}, defaults.persistent);
    }
    function getPrefix(snackType) {
        switch(snackType) {
            case 'success':
                return '<i class="material-icons text-success">check</i> ';
            case 'info':
                return '<i class="material-icons text-info">info_outline</i> ';
            case 'warning':
                return '<i class="material-icons text-warning">warning</i> ';
            case 'error':
                return '<i class="material-icons text-danger">error_outline</i> ';
            default:
                return '';
        }
    }

    var snack = {};
    snack.snacks = {};

    snack.addAutocloseSnack = function(content, snackType) {
        var options = getAutocloseSnack();
        content = getPrefix(snackType) + content;
        options.content = content;
        $.snackbar(options);
    };
    snack.addPersistentSnack = function(id, content, snackType) {
        if(snack.snacks[id]) return;
        var options = getPersistentSnack();
        content = getPrefix(snackType) + content;
        options.content = content;
        snack.snacks[id] = $.snackbar(options);
    };
    snack.closeSnack = function(id, keepAsKey) {
        if(!snack.snacks[id]) return;
        snack.snacks[id].snackbar('hide');
        if(keepAsKey !== false) {
            delete snack.snacks[id];
        }
    };

    return snack;
}]);

spacialistApp.service('modalService', ['$uibModal', 'httpGetFactory', function($uibModal, httpGetFactory) {
    var defaults = {
        backdrop: true,
        keyboard: true,
        modalFade: true,
        templateUrl: 'layouts/image-properties.html',
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
    this.editRoleModal = function(onEdit, role) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/edit-role.html',
            controller: function($uibModalInstance) {
                this.roleinfo = angular.copy(role);
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.onEdit = function(roleinfo) {
                    var changes = {};
                    if(!role) {
                        changes = roleinfo;
                    } else {
                        for(var key in role) {
                            if(role.hasOwnProperty(key)) {
                                if(role[key] != roleinfo[key]) {
                                    changes[key] = roleinfo[key];
                                }
                            }
                        }
                    }
                    onEdit(role, changes);
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
    this.warningModal = function(msg, onConfirm, onDiscard) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/warning.html',
            controller: function($uibModalInstance) {
                this.msg = msg;
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.onConfirm = function() {
                    onConfirm();
                    $uibModalInstance.dismiss('ok');
                };
                this.onDiscard = function() {
                    onDiscard();
                    $uibModalInstance.dismiss('ok');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };
    this.newContextTypeModal = function(labelCallback, onCreate) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/new-context-type.html',
            controller: function($uibModalInstance) {
                this.contextTypeTypes = [
                    { id: 0, label: 'context-type.type.context'},
                    { id: 1, label: 'context-type.type.find'}
                ];
                this.onSearch = labelCallback;
                this.onCreate = function(label, type) {
                    onCreate(label, type);
                    $uibModalInstance.dismiss('ok');
                };
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };
    this.editContextTypeModal = function(onEdit, labelCallback, ct, name) {
        var origName = name;
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/edit-contexttype.html',
            controller: function($uibModalInstance) {
                this.name = name;
                this.onSearch = labelCallback;
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.onEdit = function(newType) {
                    if(origName != newType.label) onEdit(ct, newType);
                    $uibModalInstance.dismiss('ok');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function() {}, function() {});
    };
    this.addNewAttributeModal = function(labelCallback, onCreate, datatypes) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/add-attribute.html',
            controller: function($uibModalInstance) {
                this.needsRoot = {
                    'string-sc': 1,
                    'string-mc': 1,
                    epoch: 1
                };
                this.datatypes = datatypes;
                this.onSearch = labelCallback;
                this.onCreate = function(label, datatype, parent) {
                    onCreate(label, datatype, parent);
                    $uibModalInstance.dismiss('ok');
                };
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };
    this.addAttributeToContextTypeModal = function(ct, attrs, onCreate) {
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/add-attribute-contexttype.html',
            controller: function($uibModalInstance) {
                this.ct = ct;
                this.attributes = attrs;
                this.onCreate = function(attr) {
                    onCreate(attr, ct);
                    $uibModalInstance.dismiss('ok');
                };
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
                $('#attribute-editor').css('height', '');
            } else {
                var height = newValue.height;
                var width = newValue.width;

                var headerHeight = document.getElementById('header-nav').offsetHeight;
                var addonNavHeight = 0;
                var addonNav = document.getElementById('addon-nav');
                if(addonNav) addonNavHeight = addonNav.offsetHeight;
                var containerHeight = scope.containerHeight = height - headerHeight - headerPadding - bottomPadding;
                var addonContainerHeight = scope.addonContainerHeight = containerHeight - addonNavHeight;
                var attributeEditor = document.getElementById('attribute-editor');
                if(attributeEditor) {
                    $(attributeEditor).css('height', containerHeight);
                    var heading = document.getElementById('editor-heading');
                    $('.attribute-editor-column').css('height', containerHeight - (heading.offsetHeight+headerPadding));
                }
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
        templateUrl: 'includes/tree.html',
        scope: {
            onClickCallback: '&',
            contexts: '=',
            element: '=',
            displayAttribute: '=',
            typeAttribute: '=',
            prefixAttribute: '=',
            setContextMenu: '='
        },
        controller: 'mainCtrl'
    };
});

spacialistApp.directive('imageList', function(imageService) {
    return {
        restrict: 'E',
        templateUrl: 'includes/image-list.html',
        scope: {
            onScrollLoad: '&',
            scrollContainer: '=',
            imageData: '=',
            imageType: '=',
            showTags: '=',
            searchTerms: '='
        },
        controller: 'imageCtrl',
        link: function(scope, elements, attrs) {
            scope.availableTags = imageService.availableTags;
            scope.$root.$on('image:delete:linked', function(event, args) {
                scope.tmpData.linked = [];
            });
        }
    };
});

spacialistApp.directive('formField', function($log) {
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
            scope.isEditable = typeof attrs.editable != 'undefined' && (attrs.editable.length === 0 || attrs.editable == 'true');
            scope.isDeletable = typeof attrs.deletable != 'undefined' && (attrs.deletable.length === 0 || attrs.deletable == 'true');
            scope.isOrderable = typeof attrs.orderable != 'undefined' && (attrs.orderable.length === 0 || attrs.orderable == 'true');
            if(scope.isDeletable && typeof attrs.onDelete == 'undefined') {
                throw new Error('onDelete method is missing! The on-delete attribute is mandatory if you use the deletable attribute.');
            }
            if(scope.isOrderable) {
                if(!attrs.onOrder) {
                    throw new Error('onOrder method is missing! The on-order attribute is mandatory if you use the orderable attribute.');
                }
                scope.onOrder = scope.$eval(attrs.onOrder);
                if(!scope.onOrder.up || !scope.onOrder.down) {
                    throw new Error('onOrder must be an object with two fields: up and down, which are both functions.');
                }
            }
            scope.onDelete = scope.$eval(attrs.onDelete);
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

spacialistApp.filter('imageFilter', function(searchService) {
    var foundAll = function(haystack, needle) {
        if(!needle || needle.length === 0) return true;
        return needle.every(function(v) {
            return haystack.indexOf(v) >= 0;
        });
    };

    var foundSingle = function(haystack, needle) {
        if(!haystack || haystack.length === 0) return true;
        return haystack.indexOf(needle) > -1;
    };

    var matchesAllFilters = function(item, searchTerms) {
        if(!foundAll(item.tags, searchTerms.tags)) return false;
        if(!foundSingle(searchTerms.cameras, item.cameraname)) return false;
        if(!foundSingle(searchTerms.dates, searchService.formatUnixDate(item.created*1000))) return false;
        return true;
    };

    return function(items, searchTerms) {
        var filtered = [];
        for(var i=0; i<items.length; i++) {
            var item = items[i];
            if(matchesAllFilters(item, searchTerms)) {
                filtered.push(item);
            }
        }
        return filtered;
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

spacialistApp.filter('bytes', function() {
	return function(bytes, precision) {
        var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		if(isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '0 ' + units[0];
		if(typeof precision === 'undefined') precision = 1;
		var number = Math.floor(Math.log(bytes) / Math.log(1024));
		return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) +  ' ' + units[number];
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
    var getData = function(url, data) {
        return $http.post(url, data, {
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
    return function(url, data, callback) {
        $http.post(url, data, {
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response) {
            callback(response);
        });
    };
});

spacialistApp.factory('httpGetPromise', function($http) {
    var getData = function(url) {
        return $http.get(url, {
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
    return function(url, callback) {
        $http.get(url, {
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response) {
            callback(response);
        });
    };
});

spacialistApp.factory('httpDeleteFactory', function($http) {
    return function(url, callback) {
        $http.delete(url, {
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response) {
            callback(response);
        });
    };
});

spacialistApp.factory('httpPatchFactory', function($http) {
    return function(url, callback) {
        $http.patch(url, {
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response) {
            callback(response);
        });
    };
});

spacialistApp.factory('httpPutFactory', function($http) {
    return function(url, data, callback) {
        $http.put(url, data, {
            headers: {
                'Content-Type': undefined
            }
        }).success(function(response) {
            callback(response);
        });
    };
});

spacialistApp.config(['markedProvider', function (markedProvider) {
  markedProvider.setOptions({
    gfm: true,
    tables: true,
    highlight: function (code, lang) {
      if (lang) {
        return hljs.highlight(lang, code, true).value;
      } else {
        return hljs.highlightAuto(code).value;
      }
    }
  });
  markedProvider.setRenderer({
    link: function(href, title, text) {
      return "<a href='" + href + "'" + (title ? " title='" + title + "'" : '') + " target='_blank'>" + text + "</a>";
    }
  });
}]);

spacialistApp.config(function($translateProvider) {
    $translateProvider.useStaticFilesLoader({
        files: [{
            prefix: 'l10n/',
            suffix: '.json'
        }, {
            prefix: 'l10n/project-',
            suffix: '.json'
        }]
    });
    $translateProvider.registerAvailableLanguageKeys(['en', 'de', 'fr', 'it', 'es'], {
        'de_DE': 'de',
        'de_AT': 'de',
        'de_CH': 'de',
        'en_UK': 'en',
        'en_US': 'en'
    });
    $translateProvider.determinePreferredLanguage();
    $translateProvider.useSanitizeValueStrategy('sce');
    $translateProvider.useLocalStorage();
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
    var rejectTranslationKeys = [
        "login.error.user-not-found",
        "login.error.token-not-provided",
        "login.error.token-expired",
        "login.error.token-absent",
        "login.error.token-invalid"
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
                    userService.loginError.message = rejectTranslationKeys[reasonIndex];
                } else if(rejection.status == 400 || rejection.status == 401) {
                    var $state = $injector.get('$state');
                    var userService = $injector.get('userService');
                    userService.loginError.message = 'login.error.400-or-401';
                    localStorage.removeItem('user');
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
        .state('roles', {
            url: '/roles',
            templateUrl: 'roles.html'
        })
        .state('literature', {
            url: '/literature',
            templateUrl: 'literature.html'
        })
        .state('attributes', {
            url: '/attribute-editor',
            templateUrl: 'attribute-editor.html'
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

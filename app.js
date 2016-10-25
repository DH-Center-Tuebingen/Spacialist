var spacialistApp = angular.module('tutorialApp', ['ngAnimate', 'ngRoute', 'ngMessages', 'ui-leaflet', 'ui.select', 'ngSanitize', 'pascalprecht.translate', 'ngFlag', 'ui.bootstrap', 'monospaced.mousewheel', 'ngFileUpload', 'ui.tree', 'infinite-scroll']);

spacialistApp.service('modalService', ['$uibModal', function($uibModal) {
    var defaults = {
        backdrop: true,
        keyboard: true,
        modalFade: true,
        templateUrl: 'layouts/modal-popup.html?t=' + Math.random().toString(36).slice(2),
        windowClass: 'wide-modal'
    };
    var options = {};

    this.showModal = function(customDefaults, customOptions) {
        if(!customDefaults) customDefaults = {};
        //customDefaults.backdrop = 'static';
        return this.show(customDefaults, customOptions);
    }

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

spacialistApp.directive('formField', function() {
    return {
        restrict: 'E',
        templateUrl: 'includes/inputFields.html',
        scope: {
            fields: '=',
            output: '='
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

spacialistApp.factory('storeContext', function($http) {
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
    }
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
    }
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
    }
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
    }
    return service;
});

spacialistApp.config(function($routeProvider, $locationProvider) {
    $routeProvider
        .when('/dates', {
            templateUrl: 'dates.html'
        })
        .when('/map', {
            templateUrl: 'map.html'
        })
        .when('/doc', {
            templateUrl: 'doc.html'
        })
        .when('/test', {
            templateUrl: 'test.html'
        })
        .when('/user/role/:activeRole', {
            template: '',
            controller: 'headerCtrl'
        })
        .otherwise({
            redirectTo: '/map'
        });

    //$locationProvider.html5Mode(true);
});

spacialistApp.config(function($translateProvider) {
    $translateProvider.useStaticFilesLoader({
        prefix: 'l10n/',
        suffix: '.json'
    });
    $translateProvider.determinePreferredLanguage();
    $translateProvider.useSanitizeValueStrategy('escape');
});

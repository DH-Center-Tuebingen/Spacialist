spacialistApp.controller('fileCtrl', ['fileService', 'mainService', 'snackbarService', '$translate', function(fileService, mainService, snackbarService, $translate) {
    var vm = this;

    vm.getMimeType = fileService.getMimeType;

    vm.search = {
        terms: {
            tags: []
        }
    };

    vm.addTag = function(file, item) {
        fileService.addTag(file, item);
    };

    vm.removeTag = function(file, item) {
        fileService.removeTag(file, item);
    };
}]);

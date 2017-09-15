spacialistApp.controller('bibliographyCtrl', function(literatureService) {
    var vm = this;

    vm.sortType = 'author';
    vm.sortReverse = false;
    vm.searchTerm = '';

    vm.openAddLiteratureDialog = function() {
        literatureService.openAddLiteratureDialog(vm.bibliography);
    };

    vm.importBibTexFile = function(file, invalidFiles) {
        literatureService.importBibTexFile(file, invalidFiles, vm.bibliography);
    };

    vm.deleteLiteratureEntry = function(entry) {
        literatureService.deleteLiteratureEntry(entry, vm.bibliography);
    };
});

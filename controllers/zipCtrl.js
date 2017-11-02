spacialistApp.controller('zipCtrl', ['$scope', 'httpGetFactory', function($scope, httpGetFactory) {
    $scope.zip = {
        file_id: -1,
        entries: {}
    };

    $scope.showZipContent = function(file) {
        httpGetFactory('api/file/' + file.id + '/zip', function(response) {
            for(var k in $scope.zip.entries) {
                if($scope.zip.entries.hasOwnProperty(k)) {
                    delete $scope.zip.entries[k];
                }
            }
            $scope.zip.file_id = file.id;
            for(k in response) {
                $scope.zip.entries[k] = response[k];
            }
        });
    };

    $scope.downloadFileInZip = function(file_id, file) {
        if(file.is_directory) return; // downloading of folders is not supported
        // var path = window.encodeURIComponent(file.filename);
        var path = file.filename;
        httpGetFactory('api/file/' + file_id + '/zip/filepath=' + path, function(response) {
            var encodedUri = encodeURI(response);
            var link = document.createElement("a");
            link.setAttribute("href", 'data:' + encodedUri);
            link.setAttribute("download", file.clean_filename);
            document.body.appendChild(link);
            link.click();
        });
    }
}]);

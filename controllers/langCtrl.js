spacialistApp.controller('LangCtrl', function($scope, $translate) {
        $scope.switchLang = function(key) {
            $translate.use(key);
        }

        /*
         * checks if the current language equals the param lang.
         * Current language is in xx_XX format, but param lang can be xx as well
         */
        $scope.isLangSet = function(lang) {
            var currLang = $translate.use();
            if(typeof currLang === 'undefined') return false;
            return currLang.startsWith(lang); // 'de_DE'.startsWith('de')
        }
});

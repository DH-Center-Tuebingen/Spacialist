spacialistApp.controller('LangCtrl', function($scope, $translate) {
    $scope.availableLanguages = {
        de: {
            label: 'Deutsch',
            flagCode: 'de'
        },
        en_UK: {
            label: 'English (UK)',
            flagCode: 'gb'
        },
        en_US: {
            label: 'English (US)',
            flagCode: 'us'
        },
        fr: {
            label: 'Français',
            flagCode: 'fr'
        },
        it: {
            label: 'Italiano',
            flagCode: 'it'
        },
        es: {
            label: 'Español',
            flagCode: 'es'
        }
    };

    $scope.setInitLanguage = function() {
        updateLanguage($translate.resolveClientLocale());
    };

    var updateLanguage = function(langKey) {
        if(typeof langKey == 'undefined') $scope.currentLanguage = undefined;
        else $scope.currentLanguage = $scope.availableLanguages[langKey];
    };

    $scope.switchLang = function(key) {
        var langPromise = $translate.use(key);
        if(typeof langPromise == 'object') {
            langPromise.then(function() { updateLanguage(key); });
        } else {
            updateLanguage(langPromise);
        }
    };

    /*
     * checks if the current language equals the param lang.
     * Current language is in xx_XX format, but param lang can be xx as well
     */
    $scope.isLangSet = function(lang) {
        if(typeof $scope.currentLanguage == 'undefined') return false;
        if(typeof lang == 'undefined') return false;
        return $scope.currentLanguage == $scope.availableLanguages[lang];
    };
});

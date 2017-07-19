spacialistApp.service('langService', ['$translate', 'httpGetPromise', function($translate, httpGetPromise) {
    var lang = {};

    lang.availableLanguages = {
        de: {
            label: 'Deutsch',
            flagCode: 'de'
        },
        en: {
            label: 'English',
            flagCode: 'us'
        }
        /*en_UK: {
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
        }*/
    };

    lang.currentLanguage = {
        label: '',
        flagCode: ''
    };

    lang.getCurrentLanguage = function() {
        return $translate.use();
    };

    lang.switchLanguage = function(key, concepts) {
        var langPromise = $translate.use(key);
        if(typeof langPromise == 'object') {
            langPromise.then(function() {
                updateLanguage(key, concepts);
            });
        } else {
            updateLanguage(langPromise, concepts);
        }
    };

    /*
     * checks if the current language equals the param lang.
     * Current language is in xx_XX format, but param lang can be xx as well
     */
    lang.isLangSet = function(lang) {
        if(typeof lang.currentLanguage == 'undefined') return false;
        if(typeof lang == 'undefined') return false;
        return lang.currentLanguage == lang.availableLanguages[lang];
    };

    lang.getConcepts = function(lang) {
        return httpGetPromise.getData('api/thesaurus/concept/'+lang).then(function(response) {
            return response.data;
        });
    }

    setInitLanguage();

    function setInitLanguage() {
        var storedLang = localStorage.getItem('NG_TRANSLATE_LANG_KEY');
        // check if there is a stored language
        if(storedLang === null) {
            updateLanguage($translate.resolveClientLocale());
        } else {
            updateLanguage(storedLang);
        }
    }

    function updateLanguage(langKey, concepts) {
        if(typeof langKey == 'undefined') {
            lang.currentLanguage.label = '';
            lang.currentLanguage.flagCode = '';
        } else {
            var newLang = lang.availableLanguages[langKey];
            lang.currentLanguage.label = newLang.label;
            lang.currentLanguage.flagCode = newLang.flagCode;
        }
        if(concepts) {
            lang.getConcepts(langKey).then(function(newConcepts) {
                for(var k in newConcepts) {
                    if(newConcepts.hasOwnProperty(k)) {
                        concepts[k] = newConcepts[k];
                    }
                }
            });
        }
    }

    return lang;
}]);

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

    lang.getCurrentLanguage = function() {
        return $translate.use();
    };

    lang.switchLanguage = function(langObject, langKey, concepts) {
        var langPromise = $translate.use(langKey);
        if(typeof langPromise == 'object') {
            langPromise.then(function() {
                updateLanguage(langObject, langKey, concepts);
            });
        } else {
            updateLanguage(langObject, langPromise, concepts);
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
    };

    lang.setInitLanguage = function(langObject, langKey) {
        langKey = langKey || localStorage.getItem('NG_TRANSLATE_LANG_KEY');
        // check if there is a stored language
        if(!langKey) {
            langKey = $translate.resolveClientLocale();
        }
        lang.switchLanguage(langObject, langKey);
    };

    function updateLanguage(langObject, langKey, concepts) {
        if(typeof langKey == 'undefined') {
            langObject.label = '';
            langObject.flagCode = '';
        } else {
            var newLang = lang.availableLanguages[langKey];
            langObject.label = newLang.label;
            langObject.flagCode = newLang.flagCode;
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

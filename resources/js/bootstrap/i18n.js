import { createI18n } from 'vue-i18n';
import en from '../i18n/en';
import de from '../i18n/de';

const messages = {
    en: en,
    de: de
}
const i18n = createI18n({
    legacy: false,
    locale: navigator.language,
    fallbackLocale: 'en',
    messages: messages
});

export function useI18n() {
    return i18n;
}

export function getSupportedLanguages() {
    return Object.keys(messages);
}

export default i18n;
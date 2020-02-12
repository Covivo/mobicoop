import polyglotI18nProvider from 'ra-i18n-polyglot';
import frenchMessages from 'ra-language-french';
import englishMessages from 'ra-language-english';


// domain translations
import * as domainMessages from './domainMessages';

const messages = {
    fr: { ...frenchMessages, ...domainMessages.fr },
    en: { ...englishMessages, ...domainMessages.en },

};

const i18nProviderTranslations = polyglotI18nProvider(locale => messages[locale],'fr');

export default i18nProviderTranslations;
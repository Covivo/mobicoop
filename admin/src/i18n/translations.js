import polyglotProvider from 'ra-i18n-polyglot';
import frenchMessages from 'ra-language-french';
import englishMessages from 'ra-language-english';

import * as domainMessages from './domainMessages';

const messages = {
  fr: { ...frenchMessages, ...domainMessages.fr },
  en: { ...englishMessages, ...domainMessages.en },
};
console.log({ frenchMessages, englishMessages, messages });
export default polyglotProvider((locale) => messages[locale], 'fr');

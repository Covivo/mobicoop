import polyglotProvider from 'ra-i18n-polyglot';
import frenchMessages from 'ra-language-french';
import englishMessages from 'ra-language-english';
import merge from 'lodash.merge';

import * as domainMessages from './domainMessages';

const messages = {
  fr: merge(frenchMessages, domainMessages.fr),
  en: merge(englishMessages, domainMessages.en),
};

export default polyglotProvider((locale) => messages[locale], 'fr');

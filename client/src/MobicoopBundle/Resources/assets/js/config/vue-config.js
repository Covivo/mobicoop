"use strict"

import Vue from 'vue'
import Vuetify, { VApp } from 'vuetify/lib'
import VueI18n from 'vue-i18n'
import Buefy from 'buefy'; // TODO ☣️ remove it when not needed anymore
import VueFormWizard from 'vue-form-wizard';  // TODO ☣️ remove it when not needed anymore
import * as _ from 'lodash';

// import color theme
import colorTheme from '@themes/mobicoop.json';

// import md from "material-design-icons-iconfont";
import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader
import translations from '@translations/translations.json';
import translationsClient from '@clientTranslations/translations.json';
import 'vue-form-wizard/dist/vue-form-wizard.min.css'; // TODO ☣️ remove it when not needed anymore

let translationsMerged = _.merge(translations, translationsClient);

// console.error(translationsMerged)

Vue.use(Vuetify);
Vue.use(VueI18n);
Vue.use(Buefy, { // TODO ☣️ remove it when not needed anymore
  defaultTooltipType: 'is-mobicoopgreen'
});
Vue.use(VueFormWizard); // TODO ☣️ remove it when not needed anymore


const i18n = new VueI18n({
  locale: 'fr', // set locale
  translationsMerged, // set locale messages
});

const vuetify = new Vuetify({
  icons: {
    iconfont: 'mdi', // default - only for display purposes (https://materialdesignicons.com/)
  },
  theme: colorTheme
});

export {
  VApp,
  vuetify,
  i18n,
  Vue
}

// export default new Vuetify({ ... });
"use strict"

import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import VueI18n from 'vue-i18n'
// import md from "material-design-icons-iconfont";
import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader
import messages from '../../../translations/translations.json';
import Buefy from 'buefy';


Vue.use(Vuetify);
Vue.use(Buefy); // drop it when all buefy used int he project will be removed
// Vue.use(md);
Vue.use(VueI18n);
// Vue.use(VueFormWizard);

// import traductions


const i18n = new VueI18n({
  locale: 'fr', // set locale
  messages, // set locale messages
});

const vuetify = new Vuetify({
  icons: {
    iconfont: 'mdi', // default - only for display purposes
  },
});

export {
  vuetify,
  i18n,
  Vue
}

// export default new Vuetify({ ... });
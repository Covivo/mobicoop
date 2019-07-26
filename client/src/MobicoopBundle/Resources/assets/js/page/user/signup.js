'use strict';

import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import VueFormWizard from 'vue-form-wizard';
import Vuetify from 'vuetify';
import VueI18n from 'vue-i18n'
import 'vuetify/dist/vuetify.min.css'; 
import md from "material-design-icons-iconfont"; 
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/user/signup.scss';

// Vue components
import Signupform from '../../components/Signupform';
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

// import traductions
import messages from '../../../../translations/translations.json';

Vue.use(VueI18n)
Vue.use(Buefy); 
Vue.use(Vuetify);
Vue.use(md)
Vue.use(VueFormWizard);

// Create VueI18n instance with options
const i18n = new VueI18n({
  locale: 'fr', // set locale
  messages, // set locale messages
});

new Vue({
  i18n,
  el: '#app',
  vuetify: new Vuetify(),
  components: {
    Signupform,
    Vueheader,
    Vuefooter
  }
})
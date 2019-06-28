'use strict';


import 'babel-polyfill';
import Vue from 'vue';
import VueI18n from 'vue-i18n'
import Buefy from 'buefy';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/user/updateProfile.scss';

// Vue components
import Updateprofile from '../../components/Updateprofile';

// import traductions
import messages from '../../../../translations/translations.json';

Vue.use(VueI18n)
Vue.use(Buefy); 
Vue.use(VueFormWizard);

// Create VueI18n instance with options
const i18n = new VueI18n({
  locale: 'fr', // set locale
  messages, // set locale messages
});

new Vue({
  i18n,
  el: '#update',
  components: {
    Updateprofile
  }
})
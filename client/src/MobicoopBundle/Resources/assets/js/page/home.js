'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import Vuetify from 'vuetify';
import VueI18n from 'vue-i18n'
import 'vuetify/dist/vuetify.min.css'; 
import md from "material-design-icons-iconfont"; 
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../css/page/home.scss';

// Vue components
import Homesearchform from '../components/Homesearchform';
import Vueheader from '../components/Vueheader';
import Vuefooter from '../components/Vuefooter';

// import traductions
import messages from '../../../translations/translations.json';

Vue.use(Vuetify);
Vue.use(md);
Vue.use(VueI18n);

Vue.use(Buefy);
Vue.use(VueFormWizard);
const i18n = new VueI18n({
  locale: 'fr', // set locale
  messages, // set locale messages
});

new Vue({
  i18n,
  el: '#app',
  vuetify: new Vuetify(),
  components: {
    Homesearchform,
    Vueheader,
    Vuefooter
  }
})
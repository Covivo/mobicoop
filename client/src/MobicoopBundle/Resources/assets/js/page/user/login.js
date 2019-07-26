'use strict';

import Vue from 'vue';
import Vuetify from 'vuetify';
import VueI18n from 'vue-i18n'
import 'vuetify/dist/vuetify.min.css'; 
import md from "material-design-icons-iconfont"; 
import '../../../css/page/user/login.scss';

// Vue components
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

// import traductions
import messages from '../../../../translations/translations.json';

Vue.use(VueI18n);
Vue.use(Vuetify);
Vue.use(md);

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
    Vueheader,
    Vuefooter
  }
})
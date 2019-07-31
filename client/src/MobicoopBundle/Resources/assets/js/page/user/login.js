'use strict';
import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/login.scss';

// Vue components
import Login from '../../components/user/Login';
import Vueheader from '@js/components/Vueheader';
import Vuefooter from '@js/components/Vuefooter';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Login,
    Vueheader,
    Vuefooter
  }
})
'use strict';
import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/login.scss';

// Vue components
import Login from '@js/components/user/Login';
import MHeader from '@js/components/MHeader';
import MFooter from '@js/components/MFooter';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Login,
    MHeader,
    MFooter
  }
})
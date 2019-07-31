'use strict';

import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/profile.scss';

// Vue components

// Vue components
import Profile from '@js/components/user/Profile';
import Vueheader from '@js/components/Vueheader';
import Vuefooter from '@js/components/Vuefooter';


new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Profile,
    Vueheader,
    Vuefooter
  }
})
'use strict';

import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/signup.scss';

// Vue components
import Signupform from '@js/components/Signupform';
import Vueheader from '@js/components/Vueheader';
import Vuefooter from '@js/components/Vuefooter';


new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Signupform,
    Vueheader,
    Vuefooter
  }
})
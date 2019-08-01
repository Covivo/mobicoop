'use strict';

import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/signup.scss';

// Vue components
import Signupform from '@js/components/Signupform';
import MHeader from '@js/components/MHeader';
import MFooter from '@js/components/MFooter';


new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Signupform,
    MHeader,
    MFooter
  }
})
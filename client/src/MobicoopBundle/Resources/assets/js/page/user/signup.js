'use strict';

import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/user/signup.scss';

// Vue components
import Signupform from '../../components/Signupform';
import MHeader from '../../components/MHeader';
import MFooter from '../../components/MFooter';


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
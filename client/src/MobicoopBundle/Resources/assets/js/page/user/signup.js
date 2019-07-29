'use strict';

import { Vue, vuetify, i18n } from '../../config/vue-config'
import '../../../css/page/user/signup.scss';

// Vue components
import Signupform from '../../components/Signupform';
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';


new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Signupform,
    Vueheader,
    Vuefooter
  }
})
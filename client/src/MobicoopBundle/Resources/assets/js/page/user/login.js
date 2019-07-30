'use strict';

import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/user/login.scss';

// Vue components
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Vueheader,
    Vuefooter
  }
})
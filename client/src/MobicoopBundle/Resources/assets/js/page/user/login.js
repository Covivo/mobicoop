'use strict';

import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/user/login.scss';

// Vue components
import MHeader from '../../components/MHeader';
import MFooter from '../../components/MFooter';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    MHeader,
    MFooter
  }
})
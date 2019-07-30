'use strict';

import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/user/updateProfile.scss';

// Vue components

// Vue components
import Updateprofile from '../../components/Updateprofile';
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';


new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Updateprofile,
    Vueheader,
    Vuefooter
  }
})
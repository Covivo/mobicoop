'use strict';

import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/updateProfile.scss';

// Vue components

// Vue components
import Updateprofile from '@js/components/Updateprofile';
import Vueheader from '@js/components/Vueheader';
import Vuefooter from '@js/components/Vuefooter';


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
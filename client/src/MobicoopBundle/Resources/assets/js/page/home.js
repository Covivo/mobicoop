'use strict';

import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'

// Vue components
import Homesearchform from '@js/components/Homesearchform';
import Vueheader from '@js/components/Vueheader';
import Vuefooter from '@js/components/Vuefooter';

import '@css/page/home.scss';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Homesearchform,
    Vueheader,
    Vuefooter
  }
})
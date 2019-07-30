'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/community/communities.scss';

// Vue components
import Vueheader from '@js/components/Vueheader';
import Vuefooter from '@js/components/Vuefooter';

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    VApp,
    Vueheader,
    Vuefooter
  }
})
'use strict';
import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/messages.scss';

// Vue components
import Messages from '../../components/user/Messages';
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Messages,
    Vueheader,
    Vuefooter
  }
})

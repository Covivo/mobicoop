'use strict';
import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/user/messages.scss';

// Vue components
import Messages from '../../components/user/Messages';
import MHeader from '../../components/MHeader';
import MFooter from '../../components/MFooter';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Messages,
    MHeader,
    MFooter
  }
})

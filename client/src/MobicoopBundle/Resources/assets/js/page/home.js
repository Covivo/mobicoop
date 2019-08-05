'use strict';

import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'

// Vue components
import HomeSearch from '@js/components/HomeSearch';
import MHeader from '@js/components/MHeader';
import MFooter from '@js/components/MFooter';

import '@css/page/home.scss';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    HomeSearch,
    MHeader,
    MFooter
  }
})
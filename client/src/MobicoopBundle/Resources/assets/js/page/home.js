'use strict';

import { Vue, vuetify, i18n } from '@js/config/vue-config'

import HomeSearch from '@js/components/HomeSearch';
import MHeader from '@js/components/MHeader';
import MFooter from '@js/components/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    HomeSearch,
    MHeader,
    MFooter
  }
})
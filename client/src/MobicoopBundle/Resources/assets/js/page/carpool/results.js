'use strict';

import { Vue, vuetify, i18n } from '@js/config/carpool/results'

import '@css/page/carpool/results.scss'

import Matching from '@js/components/carpool/results/Matching'
import MHeader from '@js/components/base/MHeader'
import MFooter from '@js/components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Matching,
    MHeader,
    MFooter
  }
})
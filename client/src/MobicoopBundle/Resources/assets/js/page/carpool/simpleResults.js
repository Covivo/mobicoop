'use strict'

import { Vue, vuetify, i18n } from '@js/config/carpool/simpleResults'

import '@css/page/search/simpleResults.scss'

import Occasionalresults from '@js/components/search/Occasionalresults'
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Occasionalresults,
    MHeader,
    MFooter
  }
})
'use strict';

import { Vue, vuetify, i18n } from '@js/config/carpool/results'

import '@css/page/carpool/results.scss'

import Matching from '@js/components/carpool/Matching'
import MatchingHeader from '@js/components/carpool/MatchingHeader'
import MatchingFilter from '@js/components/carpool/MatchingFilter'
import MatchingResults from '@js/components/carpool/MatchingResults'
import MatchingResult from '@js/components/carpool/MatchingResult'

import MHeader from '@js/components/base/MHeader'
import MFooter from '@js/components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Matching,
    MatchingHeader,
    MatchingFilter,
    MatchingResults,
    MatchingResult,
    MHeader,
    MFooter
  }
})
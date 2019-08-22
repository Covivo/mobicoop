'use strict';

import { Vue, vuetify, i18n } from '@js/config/carpool/simpleResults'

import '@css/page/search/simpleResults.scss'

import Occasionalresults from '@js/components/search/Occasionalresults'
import ResultCard from "../../components/search/ResultCard";
import ResultJourneyDetailedCard from "../../components/search/ResultJourneyDetailedCard";
import ResultUserDetailedCard from "../../components/search/ResultUserDetailedCard";
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Occasionalresults,
    ResultCard,
    ResultJourneyDetailedCard,
    ResultUserDetailedCard,
    MHeader,
    MFooter
  }
});
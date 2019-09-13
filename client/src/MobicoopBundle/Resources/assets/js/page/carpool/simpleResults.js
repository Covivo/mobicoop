'use strict';

import { Vue, vuetify, i18n } from '@js/config/carpool/simpleResults'

import '@css/page/search/simpleResults.scss'

import OccasionalResults from '@js/components/search/OccasionalResults'
import ResultCard from "@js/components/search/ResultCard";

import MHeader from '@js/components/base/MHeader'
import MFooter from '@js/components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    OccasionalResults,
    ResultCard,
    MHeader,
    MFooter
  }
});
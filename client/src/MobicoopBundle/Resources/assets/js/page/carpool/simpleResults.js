'use strict';

import { Vue, vuetify, i18n } from '@js/config/carpool/simpleResults'

import '@css/page/search/simpleResults.scss'

import Occasionalresults from '@js/components/search/Occasionalresults'
import ResultCard from "../../components/search/ResultCard";

import MHeader from '@js/components/base/MHeader'
import MFooter from '@js/components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Occasionalresults,
    ResultCard,
    MHeader,
    MFooter
  }
});
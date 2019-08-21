'use strict'

import { Vue, vuetify, i18n } from '@js/config/carpool/simpleResults'

import moment from 'moment'
import '@css/page/search/simpleResults.scss'

import Resultssearchform from '@components/carpool/Resultssearchform'
import Journey from '@components/carpool/Journey'
import BDatepicker from 'buefy/src/components/datepicker/Datepicker'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

// add possibility to format date by using moment
Vue.config.productionTip = false
Vue.filter('formatDate', function (value) {
  if (value) {
    return moment(String(value)).format('DD-MM-YYYY')
  }
})

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Resultssearchform,
    BDatepicker,
    Journey,
    MHeader,
    MFooter
  }
})

// dropdown. Details of results. queryselector don't return an array 
let dropdowns = [...document.querySelectorAll('.drop')]
//console.error(dropdowns);
dropdowns.map((dropdown, index) => {
  dropdown.addEventListener('click', (event) => {
    dropdown.classList.toggle('is-active');
  })
})
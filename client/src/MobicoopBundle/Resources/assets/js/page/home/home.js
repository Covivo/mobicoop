'use strict'

import { Vue, vuetify, i18n } from '@js/config/home/home'

import Home from '@components/home/Home'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Home,
    MHeader,
    MFooter
  }
})
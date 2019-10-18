'use strict'

import { Vue, vuetify, i18n } from '@js/config/carpool/publish'

import AdPublish from '@components/carpool/publish/AdPublish'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    AdPublish,
    MHeader,
    MFooter
  }
})
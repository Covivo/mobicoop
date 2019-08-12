'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/ad/create'

import AdPublish from '@js/components/AdPublish'
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

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
'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/article/article'

import '@css/page/article/article.scss'

import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    MHeader,
    MFooter
  }
})
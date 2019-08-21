'use strict'

import { Vue, vuetify, i18n } from '@js/config/article/article'

import '@css/page/article/article.scss'

import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    MHeader,
    MFooter
  }
})
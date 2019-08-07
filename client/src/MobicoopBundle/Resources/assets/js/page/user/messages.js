'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/user/messages'

import '@css/page/user/messages.scss'

import Messages from '@js/components/user/Messages'
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Messages,
    MHeader,
    MFooter
  }
})
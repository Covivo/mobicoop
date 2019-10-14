'use strict'

import { Vue, vuetify, i18n } from '@js/config/user/messages'

import '@css/page/user/messages.scss'

import Messages from '@components/user/mailbox/Messages'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

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
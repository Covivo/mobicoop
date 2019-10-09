'use strict'

import { Vue, vuetify, i18n } from '@js/config/user/passwordRecovery'

import '@css/page/user/passwordRecovery.scss'

import PasswordRecovery from '@components/user/PasswordRecovery'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    PasswordRecovery,
    MHeader,
    MFooter
  }
})
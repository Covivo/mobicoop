'use strict'

import { Vue, vuetify, i18n } from '@js/config/user/passwordRecoveryUpdate'

import '@css/page/user/passwordRecovery.scss'

import PasswordRecoveryUpdate from '@components/user/PasswordRecoveryUpdate'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    PasswordRecoveryUpdate,
    MHeader,
    MFooter
  }
})
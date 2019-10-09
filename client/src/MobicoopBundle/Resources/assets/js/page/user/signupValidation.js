'use strict'

import { Vue, vuetify, i18n } from '@js/config/user/signupValidation'

//import '@css/page/user/signupValidation.scss'

import SignupValidation from '@components/user/SignupValidation'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    SignupValidation,
    MHeader,
    MFooter
  }
})
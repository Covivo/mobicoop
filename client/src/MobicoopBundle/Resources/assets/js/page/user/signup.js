'use strict'

import { Vue, vuetify, i18n } from '@js/config/user/signup'

import '@css/page/user/signup.scss'

import Signupform from '@components/user/Signupform'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Signupform,
    MHeader,
    MFooter
  }
})
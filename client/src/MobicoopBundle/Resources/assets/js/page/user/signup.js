'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/user/signup'

import '@css/page/user/signup.scss'

import Signupform from '@js/components/Signupform'
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

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
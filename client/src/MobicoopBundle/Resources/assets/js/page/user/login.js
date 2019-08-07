'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/user/login'

import '@css/page/user/login.scss'

import Login from '@js/components/user/Login'
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Login,
    MHeader,
    MFooter
  }
})
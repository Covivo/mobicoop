'use strict'

import { Vue, vuetify, i18n } from '@js/config/user/login'

import '@css/page/user/login.scss'

import Login from '@components/user/Login'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

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
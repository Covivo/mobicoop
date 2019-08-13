'use strict'

import { Vue, vuetify, i18n } from '@js/config/user/profile'

import '@css/page/user/profile.scss'

import Profile from '@components/user/Profile'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Profile,
    MHeader,
    MFooter
  }
})
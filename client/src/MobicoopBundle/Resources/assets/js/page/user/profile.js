'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/user/profile'

import '@css/page/user/profile.scss'

import Profile from '@js/components/user/Profile'
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

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
'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/user/updateProfile'

// import '@css/page/user/profile.scss'

import UpdateProfile from '@js/components/user/updateProfile'
import MHeader from '@js/components/MHeader'
import MFooter from '@js/components/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    UpdateProfile,
    MHeader,
    MFooter
  }
})
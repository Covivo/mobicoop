'use strict'

import { Vue, vuetify, i18n } from '@js/config/page/user/updateProfile'

// import '@css/page/user/profile.scss'

import UpdateProfile from '@js/components/user/UpdateProfile'
import MHeader from '@js/components/base/MHeader'
import MFooter from '@js/components/base/MFooter'

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
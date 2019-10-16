'use strict'

import { Vue, vuetify, i18n } from '@js/config/contact/contact'

// import '@css/page/contact/contact.scss'

import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'
import Contact from '@components/contact/Contact'

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    MHeader,
    MFooter,
    Contact
  }
})

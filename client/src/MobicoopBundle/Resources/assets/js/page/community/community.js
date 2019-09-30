'use strict'

import { Vue, vuetify, i18n } from '@js/config/community/communities'

import '@css/page/community/community.scss'

// Vue components
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'
import Community from '@components/community/Community'

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    MHeader,
    Community,
    MFooter
  }
});
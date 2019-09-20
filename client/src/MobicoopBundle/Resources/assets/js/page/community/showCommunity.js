'use strict'

import { Vue, vuetify, i18n } from '@js/config/community/communities'

import '@css/page/community/showCommunity.scss'

// Vue components
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'
import CommunityDisplay from '@components/community/CommunityDisplay'

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    MHeader,
    CommunityDisplay,
    MFooter
  }
});
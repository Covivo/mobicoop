'use strict'

import { Vue, vuetify, i18n } from '@js/config/community/communities'

import '@css/page/community/showcommunity.scss'

// Vue components
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'
// import MemberList from '@components/community/MemberList'
import CommunityDisplay from '@components/community/CommunityDisplay'

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    CommunityDisplay,
    MHeader,
    MFooter
  }
});
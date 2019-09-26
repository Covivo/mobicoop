'use strict'

import { Vue, vuetify, i18n } from '@js/config/community/communities'

import '@css/page/community/createCommunity.scss'

// Vue components
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'
import CommunityCreate from "@components/community/CommunityCreate";

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    MHeader,
    MFooter,
    CommunityCreate
  }
});
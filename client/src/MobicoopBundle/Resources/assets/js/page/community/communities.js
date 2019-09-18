'use strict'

import { Vue, vuetify, i18n } from '@js/config/community/communities'

import '@css/page/community/communities.scss'

// Vue components
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'
import MemberList from '@components/community/MemberList'
import CommunityList from "@components/community/CommunityList";
import CommunityHelp from "@components/community/CommunityHelp";
import CommunityCreate from "@components/community/CommunityCreate";

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    CommunityCreate,
    CommunityHelp,
    CommunityList,
    MemberList,
    MHeader,
    MFooter
  }
})
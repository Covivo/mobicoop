'use strict';

import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/user/updateProfile.scss';

// Vue components

// Vue components
import Updateprofile from '../../components/Updateprofile';
import MHeader from '../../components/MHeader';
import MFooter from '../../components/MFooter';


new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    Updateprofile,
    MHeader,
    MFooter
  }
})
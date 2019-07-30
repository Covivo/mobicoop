'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/community/communities.scss';

// Vue components
import MHeader from '../../components/MHeader';
import MFooter from '../../components/MFooter';

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    VApp,
    MHeader,
    MFooter
  }
})
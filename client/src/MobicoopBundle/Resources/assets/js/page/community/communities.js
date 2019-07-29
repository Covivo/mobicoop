'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import { Vue, vuetify, i18n } from '../../config/vue-config'
import '../../../css/page/community/communities.scss';

// Vue components
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    Vueheader,
    Vuefooter
  }
})
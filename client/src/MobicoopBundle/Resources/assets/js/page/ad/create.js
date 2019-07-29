'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import { Vue, vuetify, i18n } from '../../config/vue-config'
import Buefy from 'buefy';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/ad/create.scss';

// Vue components
import Adcreateform from '../../components/Adcreateform';
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

Vue.use(Buefy, {
  defaultTooltipType: 'is-mobicoopgreen'
});
Vue.use(VueFormWizard);

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    Adcreateform,
    Vueheader,
    Vuefooter
  }
})
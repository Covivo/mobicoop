'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import Vue from 'vue';
import Buefy from 'buefy';
import axios from 'axios';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/home.scss';

// Vue components
import Datepicker from '../../components/Datepicker';
import Timepicker from '../../components/Timepicker';
import Adcreateform from '../../components/Adcreateform';

Vue.use(Buefy,{
  defaultTooltipType: 'is-mobicoopgreen'
});
Vue.use(VueFormWizard);

new Vue({
  el: '#app',
  components: {
    Adcreateform,
    Datepicker,
    Timepicker
  }
})
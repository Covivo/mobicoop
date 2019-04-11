'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import 'bulma-checkradio';
import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import Affix from 'vue-affix';
import axios from 'axios';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../css/page/home.scss';

// Vue components
import Homesearchform from '../components/Homesearchform';

Vue.use(Buefy,{
  defaultTooltipType: 'is-mobicoopgreen'
});
Vue.use(VueFormWizard);
  
new Vue({
  el: '#home',
  components: {
    Homesearchform
  }
})
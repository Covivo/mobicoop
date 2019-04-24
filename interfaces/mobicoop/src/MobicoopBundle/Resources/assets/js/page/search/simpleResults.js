'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import 'bulma-checkradio';
import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/search/simpleResults.scss';



// Vue components
import Journey from '../../components/Journey';

Vue.use(Buefy,{
  defaultTooltipType: 'is-mobicoopgreen'
});
Vue.use(VueFormWizard);
  
new Vue({
  el: '#simpleResults',
  components: {
    Journey
  }
})
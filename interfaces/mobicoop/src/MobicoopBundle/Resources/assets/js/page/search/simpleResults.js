'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import 'bulma-checkradio';
import 'babel-polyfill';
import Vue from 'vue';
import moment from 'moment';
import Buefy from 'buefy';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/search/simpleResults.scss';


// Vue components
import Resultssearchform from '../../components/Resultssearchform';
import Journey from '../../components/Journey'

Vue.use(Buefy,{
  defaultTooltipType: 'is-mobicoopgreen'
});
Vue.use(VueFormWizard);
// add possibility to format date by using moment
Vue.config.productionTip = false;
Vue.filter('formatDate', function(value) {
  if (value) {
    return moment(String(value)).format('DD-MM-YYYY')
  }
});
  
new Vue({
  el: '#simple',
  components: {
    Resultssearchform,
  }
})
new Vue({
  el: '#simpleResults',
  components: {
    Journey,
  }
})

// dropdown. Details of results. queryselector don't return an array 

let dropdowns = [...document.querySelectorAll('.dropdown')];
//console.error(dropdowns);
dropdowns.map ((dropdown, index) => {
  dropdown.addEventListener('click',  (event) => {
    dropdown.classList.toggle('is-active');
  })
})


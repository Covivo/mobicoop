'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import 'babel-polyfill';
import Vue from 'vue';
import moment from 'moment';
import Vuetify from 'vuetify';
import Buefy from 'buefy';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/search/simpleResults.scss';
import VueI18n from 'vue-i18n'
import 'vuetify/dist/vuetify.min.css'; 
import md from "material-design-icons-iconfont"; 

// Vue components
import Resultssearchform from '../../components/Resultssearchform';
import Journey from '../../components/Journey'
import BDatepicker from "buefy/src/components/datepicker/Datepicker"
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

// import traductions
import messages from '../../../../translations/translations.json';

Vue.use(Buefy);
Vue.use(VueFormWizard);
Vue.use(VueI18n);
Vue.use(Vuetify);
Vue.use(md);

// add possibility to format date by using moment
Vue.config.productionTip = false;
Vue.filter('formatDate', function(value) {
  if (value) {
    return moment(String(value)).format('DD-MM-YYYY')
  }
});

// Create VueI18n instance with options
const i18n = new VueI18n({
  locale: 'fr', // set locale
  messages, // set locale messages
});
  
new Vue({
  i18n,
  el: '#app',
  components: {
    Resultssearchform,
    BDatepicker,
    Journey,
    Vueheader,
    Vuefooter
  }
})

// dropdown. Details of results. queryselector don't return an array 

let dropdowns = [...document.querySelectorAll('.drop')];
//console.error(dropdowns);
dropdowns.map ((dropdown, index) => {
  dropdown.addEventListener('click',  (event) => {
    dropdown.classList.toggle('is-active');
  })
})


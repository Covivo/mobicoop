'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import moment from 'moment';
import { Vue, vuetify, i18n } from '../../config/vue-config'
import '../../../css/page/search/simpleResults.scss';

// Vue components
import Resultssearchform from '../../components/Resultssearchform';
import Journey from '../../components/Journey'
import BDatepicker from "buefy/src/components/datepicker/Datepicker"
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';


// add possibility to format date by using moment
Vue.config.productionTip = false;
Vue.filter('formatDate', function (value) {
  if (value) {
    return moment(String(value)).format('DD-MM-YYYY')
  }
});


new Vue({
  i18n,
  el: '#app',
  vuetify,
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
dropdowns.map((dropdown, index) => {
  dropdown.addEventListener('click', (event) => {
    dropdown.classList.toggle('is-active');
  })
})
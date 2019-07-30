'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import moment from 'moment';
import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/search/simpleResults.scss';

// Vue components
import Resultssearchform from '../../components/Resultssearchform';
import Journey from '../../components/Journey'
import BDatepicker from "buefy/src/components/datepicker/Datepicker"
import MHeader from '../../components/MHeader';
import MFooter from '../../components/MFooter';


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
    VApp,
    Resultssearchform,
    BDatepicker,
    Journey,
    MHeader,
    MFooter
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
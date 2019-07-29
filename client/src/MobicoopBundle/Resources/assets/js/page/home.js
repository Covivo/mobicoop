'use strict';

// any CSS you require will output into a single css file (app.css in this case)
// import 'babel-polyfill';
// import Vue from 'vue';

import { Vue, vuetify, i18n } from '../config/vue-config'
import '../../css/page/home.scss';

// Vue components
import Homesearchform from '../components/Homesearchform';
import Vueheader from '../components/Vueheader';
import Vuefooter from '../components/Vuefooter';


new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Homesearchform,
    Vueheader,
    Vuefooter
  }
})
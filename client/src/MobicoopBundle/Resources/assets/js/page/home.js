'use strict';

// any CSS you require will output into a single css file (app.css in this case)
// import 'babel-polyfill';
// import Vue from 'vue';

import { Vue, vuetify, i18n, VApp } from '../config/vue-config'

// Vue components
import HomeSearch from '../components/HomeSearch';
import MHeader from '../components/MHeader';
import MFooter from '../components/MFooter';

import '../../css/page/home.scss';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    VApp,
    HomeSearch,
    MHeader,
    MFooter
  }
})
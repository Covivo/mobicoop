'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import 'bulma-checkradio';
import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import Affix from 'vue-affix';
import '../../css/page/home.scss';

// Vue components
import Journey from '../components/Journey';
import Searchgeocoding from '../components/Searchgeocoding';
Vue.use(Affix);
Vue.use(Buefy);

let app = new Vue({
  el: '#app',
  components: {Journey,Searchgeocoding},
  data: {
    geoInfos:{
      longStart: null,
      longEnd: null,
      latStart: null,
      latEnd: null
    },
    searchUser: ""
  }
});

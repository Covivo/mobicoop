'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import '../css/app.scss';
import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';

// Vue components
import Journey from './components/Journey';
 
Vue.use(Buefy);
let app = new Vue({
  el: '#app',
  components: {Journey}
});


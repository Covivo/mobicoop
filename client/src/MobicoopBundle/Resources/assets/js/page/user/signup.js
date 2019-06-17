'use strict';


import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import '../../../css/page/user/signup.scss';

// Vue components
import Signupform from '../../components/Signupform';

Vue.use(Buefy); 

new Vue({
  el: '#signup',
  components: {
    Signupform
  }
})
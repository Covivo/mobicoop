'use strict';


import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
import '../../../css/page/user/updateProfile.scss';

// Vue components
import Updateprofile from '../../components/Updateprofile';

Vue.use(Buefy); 
Vue.use(VueFormWizard);

new Vue({
  el: '#update',
  components: {
    Updateprofile
  }
})
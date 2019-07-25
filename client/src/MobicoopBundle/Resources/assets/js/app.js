'use strict';

import Vue from 'vue';
import Vuetify from 'vuetify';
import 'vuetify/dist/vuetify.min.css'; 
import md from "material-design-icons-iconfont" 


// Vue components
import Vueheader from '../js/components/Vueheader';
import Vuefooter from '../js/components/Vuefooter';

Vue.use(Vuetify);
Vue.use(md);

new Vue({
  el: '#app',
  components: {
    Vueheader,
    Vuefooter
  }
})

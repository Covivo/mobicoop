'use strict';
import Vue from 'vue'
import Vuetify from 'vuetify'
import 'vuetify/dist/vuetify.min.css' 
import '../../../css/page/user/messages.scss';
import md from "material-design-icons-iconfont"

Vue.use(Vuetify)
Vue.use(md)

// Vue components
import Messages from '../../components/user/Messages';

new Vue({
  el: '#messages',
  components: {
    Messages
  }
})

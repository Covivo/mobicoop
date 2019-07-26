'use strict';
import Vue from 'vue'
import Vuetify from 'vuetify'
import VueI18n from 'vue-i18n'
import 'vuetify/dist/vuetify.min.css'
import '../../../css/page/user/messages.scss';
import md from "material-design-icons-iconfont"

// Vue components
import Messages from '../../components/user/Messages';
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

// import traductions
import messages from '../../../../translations/translations.json';

Vue.use(VueI18n)
Vue.use(Vuetify)
Vue.use(md)

// Create VueI18n instance with options
const i18n = new VueI18n({
  locale: 'fr', // set locale
  messages, // set locale messages
});

new Vue({
  i18n,
  el: '#app',
  vuetify: new Vuetify({
    theme: {
      themes: {
        light: {
          primary: '#81F19A',
          secondary: '#023D7F'
        },
      },
    },
  }),
  components: {
    Messages,
    Vueheader,
    Vuefooter
  }
})

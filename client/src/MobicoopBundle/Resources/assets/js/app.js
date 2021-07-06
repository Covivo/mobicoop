'use strict'

import 'babel-polyfill';
import { Vue, vuetify, i18n } from '@js/config/app'
import Vuex from 'vuex'

import components from '@js/config/components'

// Import css
import '@css/main.scss'

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: {
    gamificationNotifications: {}
  },
  mutations: {
    updateGamificationNotifications (state, gamificationNotifications) {
      state.gamificationNotifications = gamificationNotifications;
    }
  }
});

new Vue({
  el: '#app',
  vuetify,
  i18n,
  token: '',
  store,
  components: components
})
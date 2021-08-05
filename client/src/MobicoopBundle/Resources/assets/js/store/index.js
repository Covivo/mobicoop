'use strict';

import Vue from 'vue';
import Vuex from 'vuex';

import { gamificationNotifications } from './gamificationnotifications.module';

Vue.use(Vuex);

export const store = new Vuex.Store({
  modules: {
    gn:gamificationNotifications,
  },
  state: {},
  actions: {},
  mutations: {},
  getters: {}
});
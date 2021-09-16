'use strict';

import Vue from 'vue';
import Vuex from 'vuex';

import { gamificationNotifications } from './gamificationnotifications.module';
import { userPrefs } from './userprefs.module';

Vue.use(Vuex);

export const store = new Vuex.Store({
  modules: {
    gn:gamificationNotifications,
    up:userPrefs,
  },
  state: {},
  actions: {},
  mutations: {},
  getters: {}
});
'use strict';

import Vue from 'vue';
import Vuex from 'vuex';

import { gamification } from './gamification.module';
import { userPrefs } from './userprefs.module';

Vue.use(Vuex);

export const store = new Vuex.Store({
  modules: {
    g:gamification,
    up:userPrefs,
  },
  state: {},
  actions: {},
  mutations: {},
  getters: {}
});
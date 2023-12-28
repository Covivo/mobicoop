'use strict';

import Vue from 'vue';
import Vuex from 'vuex';

import { gamification } from './gamification.module';
import { userPrefs } from './userprefs.module';
import { auth } from './auth.module';
import { messages } from './messages.module';
import { sso } from './sso.module';
import { gratuity } from './gratuity.module';

Vue.use(Vuex);

export const store = new Vuex.Store({
  modules: {
    g:gamification,
    up:userPrefs,
    a:auth,
    m:messages,
    sso:sso,
    grt:gratuity,
  },
  state: {},
  actions: {},
  mutations: {},
  getters: {}
});

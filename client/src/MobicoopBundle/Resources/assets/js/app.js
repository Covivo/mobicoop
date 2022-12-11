'use strict'

import 'babel-polyfill';
import { Vue, vuetify, i18n } from '@js/config/app'
import { store } from './store';

import components from '@js/config/components'

import { Incentive } from '@js/utils/eec-incentive';

// Import css
import '@css/main.scss'

new Incentive();

new Vue({
  el: '#app',
  vuetify,
  i18n,
  token: '',
  store,
  components: components
})

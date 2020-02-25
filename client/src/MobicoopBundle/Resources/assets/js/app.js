'use strict'

import 'babel-polyfill';
import { Vue, vuetify, i18n } from '@js/config/app'

import components from '@js/config/components'

// Import css
import '@css/main.scss'

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: components
})
'use strict'
// No overcharge, only import app.js from MobicoopBundle
// import '@js/app.js';

// Else
// Import base css
import '@css/main.scss';
// Import custom css
import '@clientCss/main.scss';

// If you want to overcharge, overcharge the app.js file that loads vue js, you need to reimport everything
// You can overcharge vuetify, i18n etc, juste create the files on the instance and import them in place of config/app.js ones from bundle
import 'babel-polyfill';
import { Vue, vuetify, i18n } from '@js/config/app'

import {merge} from 'lodash'
import bundleComponents from '@js/config/components'
import clientComponents from '@clientJs/config/components'
import { store } from '@js/store'

let components = merge(bundleComponents, clientComponents);

new Vue({
  el: '#app',
  vuetify,
  i18n,
  store,
  components: components
})

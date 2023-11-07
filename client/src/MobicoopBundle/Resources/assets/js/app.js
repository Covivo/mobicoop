'use strict'

import 'babel-polyfill';
import { Vue, vuetify, i18n } from '@js/config/app'
import { store } from './store';

import components from '@js/config/components'
import * as Sentry from "@sentry/vue";

// Import css
import '@css/main.scss'

let env = 'production';
if (window.location.href.indexOf(".test.") >= 0){
  env = 'test';
}
if (window.location.href.indexOf("localhost") >= 0){
  env = 'dev';
}




Sentry.init({
  Vue,
  dsn: "https://757990e3434043f8895ffa6f320a838f@sentry.mobicoop.io/3",
  initialScope: {
    tags: { "instance": "mobicoop" },
  },
  integrations: [
    new Sentry.BrowserTracing({
      // Set `tracePropagationTargets` to control for which URLs distributed tracing should be enabled
      tracePropagationTargets: [],
    }),
  ],
  // Set tracesSampleRate to 1.0 to capture 100%
  // of transactions for performance monitoring.
  // We recommend adjusting this value in production
  tracesSampleRate: 1.0,
  environment: env,
});


new Vue({
  el: '#app',
  vuetify,
  i18n,
  token: '',
  store,
  components: components
})

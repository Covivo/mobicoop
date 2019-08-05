'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import { Vue, vuetify, i18n, VApp } from '@js/config/vue-config'
import '@css/page/article/article.scss';

// Vue components
import MHeader from '@js/components/MHeader';
import MFooter from '@js/components/MFooter';

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    VApp,
    MHeader,
    MFooter
  }
})
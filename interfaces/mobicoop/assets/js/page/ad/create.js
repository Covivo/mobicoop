'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import 'bulma-checkradio';
import 'babel-polyfill';
import Vue from 'vue';
import Buefy from 'buefy';
import Affix from 'vue-affix';
import '../../../css/page/home.scss';

// Vue components
import Mautocomplete from '../../components/Autocomplete';

Vue.use(Affix);
Vue.use(Buefy);
new Vue({
    el: '#app',
    components: { Mautocomplete},
});
'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import Vue from 'vue';
import Buefy from 'buefy';
import '../../../css/page/home.scss';

// Vue components
import Geocomplete from '../../components/Geocomplete';
import Datepicker from '../../components/Datepicker';
import Timepicker from '../../components/Timepicker';
import Vradio from '../../components/Vradio';

Vue.use(Buefy);

new Vue({
  el: '#app',
  components: { Geocomplete, Datepicker, Timepicker, Vradio},
  props: {
    srole : {
      type: Number,
      default: null
    },
    sfrequency : {
      type: Number,
      default: null
    },
    stype : {
      type: Number,
      default: null
    }
  }
})
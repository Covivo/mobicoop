'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import Vue from 'vue';
import Buefy from 'buefy';
import '../../../css/page/home.scss';

// Vue components
import Geocomplete from '../../components/Geocomplete';
import Datepicker from '../../components/Datepicker';
import Timepicker from '../../components/Timepicker';
// import Vradio from '../../components/Vradio';

Vue.use(Buefy);

new Vue({
  delimiters: ['${', '}'],
  el: '#app',
  'template': '#ad-create-template',
  components: { Geocomplete, Datepicker, Timepicker},
  props:{
    frequency: {
      type: String,
      default: ''
    },
    role: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: ''
    },
    outward: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      timeStart: new Date(),
      timeReturn: new Date(),
      days: ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche']
    };
  },
  mounted(){
    console.log('freq', this.outward);
  }
})
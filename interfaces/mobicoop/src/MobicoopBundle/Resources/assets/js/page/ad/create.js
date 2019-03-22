'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import Vue from 'vue';
import Buefy from 'buefy';
import axios from 'axios';
import '../../../css/page/home.scss';

// Vue components
import Geocomplete from '../../components/Geocomplete';
import Datepicker from '../../components/Datepicker';
import Timepicker from '../../components/Timepicker';
import AdCreateForm from '../../components/AdCreateForm';
// import Vradio from '../../components/Vradio';

console.log(AdCreateForm)

Vue.use(Buefy);

new Vue({
  delimiters: ['${', '}'],
  el: '#adFormCreate',
  components: { Geocomplete, Datepicker, Timepicker, AdCreateForm }
})
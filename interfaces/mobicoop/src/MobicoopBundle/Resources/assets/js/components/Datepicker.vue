<template>
  <div>
    <flat-pickr 
      v-model="date"
      :config="config"                                                          
      :class="dclass" 
      :name="name"
      :value="vdate"
      >
    </flat-pickr>
  </div>
</template>

<script>
  import flatPickr from 'vue-flatpickr-component';
  import 'flatpickr/dist/flatpickr.css';
  import {French} from 'flatpickr/dist/l10n/fr.js';
  const defaultString = {
    type: String,
    default: ''
  }
  
  export default {   
    props: {
      name: defaultString,
      dclass: defaultString,
      vdate: defaultString,
      date: defaultString
    }, 
    data () {
      return {
        // Get more form https://chmln.github.io/flatpickr/options/
        config: {
          minDate: "today",
          enableTime: false,
          altFormat: 'd/m/Y',
          altInput: true,
          dateFormat: 'Y-m-d',
          locale: French
        }       
      }
    },
    name: "datepicker",
    components: {
      flatPickr
    }
  }
</script>

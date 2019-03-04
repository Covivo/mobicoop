<template>
  <div>
    <flat-pickr 
      v-model="date"
      :config="config"                                                          
      :class="pclass" 
      :name="pname"
      :value="pdate"
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
      pname: defaultString,
      pclass: defaultString,
      pdate: defaultString
    }, 
    data () {
      return {
        // Get more form https://chmln.github.io/flatpickr/options/
        config: {
          minDate: "today",
          enableTime: true,
          altFormat: 'd/m/Y H:i',
          altInput: true,
          dateFormat: 'Y-m-d H:i',
          time_24hr: true,
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

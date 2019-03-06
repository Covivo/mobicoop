<template>
  <div>
    <flat-pickr 
      v-model="time"
      :config="config"                                                          
      :class="tclass" 
      :name="name"
      :value="vtime"
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
      tclass: defaultString,
      time: defaultString,
      vtime: defaultString
    }, 
    data () {
      return {
        // Get more form https://chmln.github.io/flatpickr/options/
        config: {
          enableTime:true,
          noCalendar: true,
          dateFormat: "H:i",
          time_24hr: true,
          altFormat: 'H:i',
          altInput: true,
          locale: French
        }       
      }
    },
    name: "timepicker",
    components: {
      flatPickr
    }
  }
</script>

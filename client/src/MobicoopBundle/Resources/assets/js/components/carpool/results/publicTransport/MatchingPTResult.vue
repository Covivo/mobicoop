<template>
  <!-- Matching PT result -->
  <div v-if="ptResult">
    <v-row>
      {{ departureLabel }}
    </v-row>
    <v-row>
      {{ arrivalLabel }}
    </v-row>
  </div>
</template>
<script>

export default {
  props:{
    ptResult: {
      type: Object,
      default:null
    }
  },
  data(){
    return {
    }
  },
  computed: {
    departureLabel(){
      return this.ptResult.pTDeparture.address.displayLabel[0];
    },
    arrivalLabel(){
      return this.ptResult.pTArrival.address.displayLabel[0];
    }
  },
}
</script>
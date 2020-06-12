<template>
  <div>
    <v-row>
      <v-col cols="2">
        <v-icon>mdi-home</v-icon>
      </v-col>
      <v-col cols="10">
        {{ departureLabel }}
      </v-col>
    </v-row>
    
    <v-row
      v-for="(ptLeg,index) in ptResult.pTLegs"
      :key="index"
      align="center"
    >
      <v-col
        cols="2"
      >
        <MatchingPTResultLeg
          :pt-leg="ptLeg"
          :arrow="false"
        />
      </v-col>
      <v-col cols="10">
        le détail...
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="2">
        <v-icon>mdi-flag-checkered</v-icon>
      </v-col>
      <v-col cols="10">
        {{ arrivalLabel }}
      </v-col>
    </v-row>
  </div>
</template>
<script>
import MatchingPTResultLeg from "@components/carpool/results/publicTransport/MatchingPTResultLeg";
export default {
  components:{
    MatchingPTResultLeg
  },
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
  computed:{
    departureLabel(){
      return this.ptResult.pTDeparture.address.displayLabel[0];
    },
    arrivalLabel(){
      return this.ptResult.pTArrival.address.displayLabel[0];
    }
  },
  methods: {
    icon(currentLeg){
      return currentLeg.travelMode.mdiIcon;
    }
  }
}
</script>
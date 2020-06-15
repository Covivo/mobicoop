<template>
  <div>
    <v-row>
      <v-col :cols="nbColumn1">
        <v-icon>mdi-home</v-icon>
      </v-col>
      <v-col :cols="nbColumn2">
        {{ departureTime }}
      </v-col>
      <v-col :cols="nbColumn3">
        {{ departureLabel }}
      </v-col>
    </v-row>
    
    <v-row
      v-for="(ptLeg,index) in ptResult.pTLegs"
      :key="index"
      align="center"
    >
      <v-col
        :cols="nbColumn1"
      >
        <MatchingPTResultLeg
          :pt-leg="ptLeg"
          :arrow="false"
        />
      </v-col>
      <v-col :cols="nbColumn2">
        {{ travelTime(ptLeg) }}
      </v-col>
      <v-col :cols="nbColumn3">
        <MatchingPTResultDetailsLeg
          :pt-leg="ptLeg"
          :last="(index==ptResult.pTLegs.length-1) ? true : false"
        />
      </v-col>
    </v-row>
    <v-row>
      <v-col :cols="nbColumn1">
        <v-icon>mdi-flag-checkered</v-icon>
      </v-col>
      <v-col :cols="nbColumn2">
        {{ arrivalTime }}
      </v-col>
      <v-col :cols="nbColumn3">
        {{ arrivalLabel }}
      </v-col>
    </v-row>
  </div>
</template>
<script>
import moment from "moment";
import MatchingPTResultLeg from "@components/carpool/results/publicTransport/MatchingPTResultLeg";
import MatchingPTResultDetailsLeg from "@components/carpool/results/publicTransport/MatchingPTResultDetailsLeg";
export default {
  components:{
    MatchingPTResultLeg,
    MatchingPTResultDetailsLeg
  },
  props:{
    ptResult: {
      type: Object,
      default:null
    }
  },  
  data(){
    return {
      nbColumn1:2,
      nbColumn2:1,
      nbColumn3:9
    }
  },
  computed:{
    departureLabel(){
      return this.ptResult.pTDeparture.address.displayLabel[0];
    },
    departureTime(){
      return moment.utc(this.ptResult.pTDeparture.date).format("HH:mm");
    },
    arrivalLabel(){
      return this.ptResult.pTArrival.address.displayLabel[0];
    },
    arrivalTime(){
      return moment.utc(this.ptResult.pTArrival.date).format("HH:mm");
    },
  },
  methods: {
    icon(currentLeg){
      return currentLeg.travelMode.mdiIcon;
    },
    travelTime(currentLeg){
      return moment.utc(currentLeg.pTDeparture.date).format("HH:mm")+" "+moment.utc(currentLeg.pTArrival.date).format("HH:mm");
    }
  }
}
</script>
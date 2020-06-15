<template>
  <div>
    <v-row v-if="ptLeg.travelMode && ptLeg.travelMode.name=='WALK'">
      <v-col cols="12">
        <div
          v-if="!last"
          v-html="$t('walkFromToStop',{'departure':departure,'arrival':arrival})"
        />
        <div
          v-else
          v-html="$t('walkFromToDestination',{'departure':departure,'arrival':arrival})"
        />
        <div>{{ $t('estimatedDuration',{'duration':humanReadableDuration(ptLeg.duration)}) }}</div>
      </v-col>
    </v-row>
    <v-row v-else>
      <v-col cols="12">
        autre
      </v-col>
    </v-row>
  </div>
</template>
<script>
import Translations from "@translations/components/carpool/results/publicTransport/MatchingPTResultDetailsLeg.json";
export default {
  props:{
    ptLeg: {
      type: Object,
      default:null
    },
    last: {
      type: Boolean,
      default: false
    }
  },  
  i18n: {
    messages: Translations,
  },
  data(){
    return {
    }
  },
  computed: {
    departure(){
      return this.ptLeg.pTDeparture.name;
    },
    arrival(){
      return this.ptLeg.pTArrival.name;
    }
  },
  methods:{
    humanReadableDuration(durationInSeconds){
      let measuredTime = new Date(null);
      measuredTime.setSeconds(durationInSeconds); // specify value of SECONDS
      let duration = measuredTime.toISOString().substr(11, 8).split(":");

      let returnChain = "";
      if(parseInt(duration[0])!=0){
        returnChain = parseInt(duration[0])+"h";
      }
      if(parseInt(duration[1])!=0){
        returnChain += parseInt(duration[1])+"m";
      }
      if(parseInt(duration[2])!=0){
        returnChain += parseInt(duration[2])+"s";
      }

      return returnChain;        
    }
  }
}
</script>
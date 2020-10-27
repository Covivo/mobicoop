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
    <v-row v-else-if="ptLeg.travelMode && ptLeg.travelMode.name=='WAITING'">
      <v-col cols="12">
        <div>{{ $t('waiting',{'duration':humanReadableDuration(ptLeg.duration)}) }}</div>
      </v-col>
    </v-row>
    <v-row v-else>
      <v-col cols="12">
        <div v-if="ptLeg.pTLine">
          <span class="primary--text">{{ ptLeg.pTLine.number }}</span> {{ ptLeg.pTLine.name }}
        </div>
        <div>{{ $t('direction') }} <span class="primary--text">{{ ptLeg.direction }}</span></div>
        <div v-if="departure || arrival">
          <span v-if="departure">{{ $t('hopOn') }} <span class="primary--text">{{ departure }}</span></span> <span v-if="arrival">{{ $t('hopOff') }} <span class="primary--text">{{ arrival }}</span></span>
        </div>
        <div>{{ $t('estimatedDuration',{'duration':humanReadableDuration(ptLeg.duration)}) }}</div>
      </v-col>
    </v-row>
  </div>
</template>
<script>

import { merge } from "lodash";
import {messages_fr, messages_en} from "@translations/components/carpool/results/publicTransport/MatchingPTResultDetailsLeg/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/carpool/results/publicTransport/MatchingPTResultDetailsLeg/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

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
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  data(){
    return {
    }
  },
  computed: {
    departure(){
      return (this.ptLeg.pTDeparture.address.displayLabel && this.ptLeg.pTDeparture.address.displayLabel.length>0) ? this.ptLeg.pTDeparture.address.displayLabel[0] : this.ptLeg.pTDeparture.address.name;
    },
    arrival(){
      return (this.ptLeg.pTArrival.address.displayLabel && this.ptLeg.pTArrival.address.displayLabel.length>0) ? this.ptLeg.pTArrival.address.displayLabel[0] : this.ptLeg.pTArrival.address.name;
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
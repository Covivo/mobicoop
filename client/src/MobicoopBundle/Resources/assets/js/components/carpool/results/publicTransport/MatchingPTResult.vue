<template>
  <!-- Matching PT result -->
  <div
    v-if="ptResult"
    class="grey lighten-3 pa-3"
  >
    <v-row
      align="center"
      
      dense
    >
      <v-col cols="10">
        {{ departureLabel }}
      </v-col>
      <v-col
        cols="2"
        class="text-right"
      >
        {{ journeyDuration }}
      </v-col>
    </v-row>
    <v-row
      align="center"
      dense
    >
      <v-col cols="12">
        <MatchingPTResultSummary :pt-legs="ptResult.pTLegs" />
      </v-col>
    </v-row>
    <v-row
      align="center"
      dense
    >
      <v-col cols="12">
        {{ arrivalLabel }}
      </v-col>
    </v-row>
    <v-row
      align="center"
      dense
    >
      <v-col cols="12">
        <v-expansion-panels
          flat
          hover
          focusable
        >
          <v-expansion-panel>
            <v-expansion-panel-header class="text-right">
              {{ $t('details') }}
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <MatchingPTResultDetails :pt-result="ptResult" />
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
    </v-row>    
  </div>
</template>
<script>
import moment from "moment";
import MatchingPTResultSummary from "@components/carpool/results/publicTransport/MatchingPTResultSummary";
import MatchingPTResultDetails from "@components/carpool/results/publicTransport/MatchingPTResultDetails";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/results/publicTransport/MatchingPTResult/";

export default {
  components:{
    MatchingPTResultSummary,
    MatchingPTResultDetails
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    ptResult: {
      type: Object,
      default:null
    }
  },
  data(){
    return {
      locale: localStorage.getItem("X-LOCALE"),
    }
  },
  computed: {
    departureLabel(){
      return this.ptResult.pTDeparture.address.displayLabel[0]+" "+this.$t('at')+" "+moment.utc(this.ptResult.pTDeparture.date).format("HH:mm");
    },
    arrivalLabel(){
      return this.ptResult.pTArrival.address.displayLabel[0]+" "+this.$t('at')+" "+moment.utc(this.ptResult.pTArrival.date).format("HH:mm");
    },
    journeyDuration(){
      return this.secondsToHms(this.ptResult.duration);
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods:{ 
    secondsToHms(d) {
      d = Number(d);
      let h = Math.floor(d / 3600);
      let m = Math.floor(d % 3600 / 60);
      //let s = Math.floor(d % 3600 % 60);

      let hDisplay = h > 0 ? h + (h == 1 ? " h " : " h ") : "";
      let mDisplay = m > 0 ? m + (m == 1 ? " min " : " mins ") : "";
      //let sDisplay = s > 0 ? s + (s == 1 ? " second" : " seconds") : "";
      let sDisplay = '';

      return hDisplay + mDisplay + sDisplay; 
    }  
  }
}
</script>
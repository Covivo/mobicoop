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
      <v-col cols="12">
        {{ departureLabel }}
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
              <MatchingPTResultDetails :pt-legs="ptResult.pTLegs" />
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
import Translations from "@translations/components/carpool/results/publicTransport/MatchingPTResult.json";
export default {
  components:{
    MatchingPTResultSummary,
    MatchingPTResultDetails
  },
  i18n: {
    messages: Translations,
  },
  props:{
    ptResult: {
      type: Object,
      default:null
    }
  },
  data(){
    return {
      locale: this.$i18n.locale,
    }
  },
  computed: {
    departureLabel(){
      return this.ptResult.pTDeparture.address.displayLabel[0]+" "+this.$t('at')+" "+moment.utc(this.ptResult.pTDeparture.date).format("HH:mm");
    },
    arrivalLabel(){
      return this.ptResult.pTArrival.address.displayLabel[0]+" "+this.$t('at')+" "+moment.utc(this.ptResult.pTArrival.date).format("HH:mm");
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
}
</script>
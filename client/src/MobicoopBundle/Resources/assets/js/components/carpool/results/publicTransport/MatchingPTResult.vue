<template>
  <!-- Matching PT result -->
  <div v-if="ptResult">
    <v-row>
      {{ departureLabel }}
    </v-row>
    <v-row>
      <MatchingPTResultSummary :pt-legs="ptResult.pTLegs" />
    </v-row>
    <v-row>
      {{ arrivalLabel }}
    </v-row>
  </div>
</template>
<script>
import moment from "moment";
import MatchingPTResultSummary from "@components/carpool/results/publicTransport/MatchingPTResultSummary";
import Translations from "@translations/components/carpool/results/publicTransport/MatchingPTResult.json";
export default {
  components:{
    MatchingPTResultSummary
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
      return this.ptResult.pTDeparture.address.displayLabel[0]+" "+this.$t('at')+" "+moment(this.ptResult.pTDeparture.date).format("HH:mm");
    },
    arrivalLabel(){
      return this.ptResult.pTArrival.address.displayLabel[0]+" "+this.$t('at')+" "+moment(this.ptResult.pTArrival.date).format("HH:mm");
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
}
</script>
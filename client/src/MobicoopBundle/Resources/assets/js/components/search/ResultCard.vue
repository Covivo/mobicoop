<template>
  <v-card 
    flat
  >
    <result-journey-detailed-card 
      :origin="origin"
      :destination="destination"
      :date="date"
      :carpool-results="carpoolResults"
    />
    <v-row
      justify="center"
    >
      <v-divider class="divider-width" />
    </v-row>
    <result-user-detailed-card 
      :carpool-results="carpoolResults"
    />
  </v-card>
</template>
<script>
import ResultJourneyDetailedCard from "./ResultJourneyDetailedCard";
import ResultUserDetailedCard from "./ResultUserDetailedCard";
export default {
  name: "ResultCard",
  components:{
    ResultJourneyDetailedCard,
    ResultUserDetailedCard
  },
  props: {
    origin: {
      type: String,
      default: null
    },
    destination: {
      type: String,
      default: null
    },
    originLatitude: {
      type: String,
      default: null
    },
    originLongitude: {
      type: String,
      default: null
    },
    destinationLatitude: {
      type: String,
      default: null
    },
    destinationLongitude: {
      type: String,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    matchingSearchUrl: {
      type: String,
      default: null
    },
    carpoolResults: {
      type: Object,
      default: null
    }
  },
}
</script>

<style scoped>
    .divider-width{
        max-width: 730px;
    }
</style>
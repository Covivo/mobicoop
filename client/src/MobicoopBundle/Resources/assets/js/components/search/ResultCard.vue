<template>
  <v-content>
    <v-container
      v-for="(matching,index) in matchings"
      :key="index"
    >
      <v-card
        flat
      >
        <result-journey-detailed-card 
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching"
        />
        <v-row
          justify="center"
        >
          <v-divider class="divider-width" />
        </v-row>
        <result-user-detailed-card 
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching"
        />
      </v-card>
    </v-container>
  </v-content>
</template>
<script>
import moment from "moment";
import 'moment/locale/fr';
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
  data() {
    return {      
      matchings: []
    }
  },
  watch: {
    carpoolResults(){
      // this.matchings=[this.carpoolResults.matchingRequests].concat([this.carpoolResults.matchingOffers]);
      // this.matchings=this.carpoolResults.matchingRequests.concat(this.carpoolResults.matchingOffers);
      this.matchings=this.carpoolResults.matchingRequests
    }
  }
}
</script>

<style scoped>
    .divider-width{
        max-width: 730px;
    }
</style>
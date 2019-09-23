<template>
  <v-content>
    <v-container
      v-for="(matching,index) in matchings"
      :key="index"
    >
      <v-card
        flat
      >
        <driver-passenger
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalRequest"
          :regular="regular"
        />
        <driver-passenger
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalOffer"
          :regular="regular"
        />
        <!-- display result-journey-detailed card - proposal Request and proposalOffer in array(matchings) order-->
        <result-journey-detailed-card
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalRequest"
        />
        <result-journey-detailed-card 
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalOffer"
          :regular="regular"
        />
        <v-row
          justify="center"
        >
          <v-divider class="divider-width" />
        </v-row>

        <!-- display result-user-detailed card - proposal Request and proposalOffer in array(matchings) order-->
        <result-user-detailed-card  
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalRequest" 
          :regular="regular"
        />   
        <v-row
          justify="center"
        />
        <result-user-detailed-card  
          :origin-latitude="originLatitude"
          :origin-longitude="originLongitude"
          :destination-latitude="destinationLatitude"
          :destination-longitude="destinationLongitude"
          :origin="origin"
          :destination="destination"
          :date="date"
          :carpool-results="carpoolResults"
          :matching-search-url="matchingSearchUrl"
          :matching="matching.proposalOffer" 
          :regular="regular"
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
import DriverPassenger from "./DriverPassenger";

export default {
  name: "ResultCard",
  components:{
    ResultJourneyDetailedCard,
    ResultUserDetailedCard,
    DriverPassenger
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
    },
    regular: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {      
      matchings: []
    }
  },
  watch: {
    carpoolResults(){
      // this.matchings.push(this.carpoolResults.matchingRequests,this.carpoolResults.matchingOffers);
      this.matchings=this.carpoolResults.matchingOffers.concat(this.carpoolResults.matchingRequests);
      // this.matchings=this.carpoolResults
    }
  }
}
</script>

<style scoped>
    .divider-width{
        max-width: 730px;
    }
</style>
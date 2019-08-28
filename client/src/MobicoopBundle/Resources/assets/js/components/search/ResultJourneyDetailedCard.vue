<template>
  <v-content>
    <v-container
      fluid
      class="journey-details-height mb-3"
    >
      <v-row>
        <!-- avatar --> 
        <v-col
          v-if="passenger"
          justify-space-around
        >
          <v-avatar
            color="primary"
          >
            <v-icon dark>
              mdi-account-supervisor
            </v-icon>
          </v-avatar>
        </v-col>

        <v-col
          v-else-if="driver"
          justify-space-around
        >
          <v-avatar
            color="secondary"
          >
            <v-icon dark>
              mdi-steering
            </v-icon>
          </v-avatar>
        </v-col>

        <!-- hour -->
        <v-col
          class="text-address-size"
        >
          <v-row
            top
          >
            <h3>09h00</h3>
          </v-row>

          <!-- date -->
          <v-row
            class="date-uppercase"
          >  
            <p>
              {{ displaydate(date) }}
            </p>
          </v-row>  
        </v-col>

        <!-- origin -->
        <v-col
          class="text-left"
        >
          <v-row
            class="text-city-size"
          >
            {{ origin }}
            <!-- {{ splitCityOrigin() }} -->
          </v-row>
          <v-row
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            <!-- {{ splitAddressOrigin() }} -->
          </v-row>
        </v-col> 

        <!-- icon --> 
        <v-col>
          <v-icon
            color="primary"
            size="32"
          >
            mdi-ray-start-end
          </v-icon>
        </v-col> 

        <!-- destination --> 
        <v-col
          class="text-left"
        >
          <v-row
            class="text-city-size"
          >
            {{ destination }}
            <!-- {{ splitCityDestination() }} -->
          </v-row>
          <v-row
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            <!-- {{ splitAddressDestination() }} -->
          </v-row>
        </v-col> 

        <!--seats --> 
        <v-col>
          <p>3 places</p>
        </v-col>

        <!-- price --> 
        <v-col>
          <h3>50€</h3>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>
import moment from "moment";

export default {
  name: "ResultJourneyDetailedCard",
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
    carpoolResults: {
      type: Object,
      default: null
    },
    matchingSearchUrl: {
      type: String,
      default: null
    },
  },
  data: function() {
    return {
      resultAddressOrigin: null,
      resultCityOrigin:  null,
      resultAddressDestination: null,
      resultCityDestination:  null,
      driver: false,
      passenger: true,
    };
  },
   
  methods : {
    displaydate(date){
      return moment (new Date(date)).utcOffset("+00:00").format('ddd DD/MM ')
    },
    
    // TODO : FIND AN OTHER METHOD, JUST FOR THE MOMENT :: BAD [" ... "], NOT OPTIMIZED
    // splitCityOrigin () {
    //   this.resultCityOrigin= this.origin.split(', ')
    //   return this.city= this.resultCityOrigin.slice(1)
    // },
    // splitAddressOrigin () {
    //   this.resultAddressOrigin = this.origin.split(', ')
    //   return this.address = this.resultAddressOrigin.slice(0,1)
    // },
    // splitCityDestination () {
    //   this.resultCityDestination = this.destination.split(', ')
    //   return this.city= this.resultCityDestination.slice(1)
    // },
    // splitAddressDestination () {
    //   this.resultAddressDestination = this.destination.split(', ')
    //   return this.address = this.resultAddressDestination.slice(0,1)
    // },
  }  
}
</script>

<style scoped>
  .text-city-size {
  font-size: 16px;
    font-weight: bold;
  }
  .text-address-size {
  font-size: 14px;
  }

  .date-uppercase {
        text-transform: capitalize;
  }

  .journey-details-height{
    height: 100px
  }
</style>
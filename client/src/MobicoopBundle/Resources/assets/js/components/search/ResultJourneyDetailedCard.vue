<template>
  <v-content>
    <v-container fluid>
      <v-row>
        <!-- avatar --> 
        <v-col
          justify-space-around
        >
          <v-avatar
            color="primary"
          />
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
            {{ splitCityOrigin() }}
          </v-row>
          <v-row
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            {{ splitAddressOrigin() }}
          </v-row>
        </v-col> 

        <!-- icon --> 
        <v-col>
          <v-icon
            :color="secondary"
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
            {{ splitCityDestination() }}
          </v-row>
          <v-row
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            {{ splitAddressDestination() }}
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

      <!-- TEST DRIVER PASSENGER WITH A BUTTON --> 
      <v-btn
        color="primary"
        fab
        large
        depressed
        @click="testmethod('driver')"
      >
        <v-icon>mdi-account-supervisor</v-icon><br>
      </v-btn>

      <v-btn
        color="secondary"
        fab
        large
        depressed
        @click="testmethod('passenger')"
      >
        <v-icon>mdi-steering</v-icon><br>
      </v-btn>
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
      city: null,
      address: null,
    };
  },
   
  methods : {
    displaydate(date){
      return moment (new Date(date)).utcOffset("+00:00").format('ddd DD/MM ')
    },
    // TODO : FIND AN OTHER METHOD, JUST FOR THE MOMENT :: BAD [" ... "], NOT OPTIMIZED
    splitCityOrigin () {
      this.resultCityOrigin= this.origin.split(', ')
      return this.city= this.resultCityOrigin.slice(1)
    },
    splitAddressOrigin () {
      this.resultAddressOrigin = this.origin.split(', ')
      return this.address = this.resultAddressOrigin.slice(0,1)
    },
    splitCityDestination () {
      this.resultCityDestination = this.destination.split(', ')
      return this.city= this.resultCityDestination.slice(1)
    },
    splitAddressDestination () {
      this.resultAddressDestination = this.destination.split(', ')
      return this.address = this.resultAddressDestination.slice(0,1)
    },
    // TODO : FIND AN METHOD IN ORDER CHANGE ICON IF PASSENGER,DRIVER OR BOTH
    testmethod: function (message) {
      alert(message)
    }
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
  </style>
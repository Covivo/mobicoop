<template>
  <v-content>
    <v-container fluid>
      <v-list-item>
        <!-- avatar --> 

        <v-list-item-avatar
          v-if="passenger"
          color="primary"
          size="60"
          class="ml-2"
        >
          <v-icon
            dark
            size="35"
          >
            mdi-account-supervisor
          </v-icon>
        </v-list-item-avatar>

        <v-list-item-avatar
          v-else-if="driver"
          color="secondary"
          size="60"
          class="ml-2"
        >
          <v-icon
            dark
            size="35"
          >
            mdi-steering
          </v-icon>
        </v-list-item-avatar>

        <!-- Hour & Date -->
        <v-list-item-content
          class="ml-2"
        >
          <v-list-item-title
            class="text-address-size"
          >
            <h3>09h00</h3>
          </v-list-item-title>
          <v-list-item-title class="date-uppercase">
            {{ displaydate(date) }}
          </v-list-item-title>
        </v-list-item-content>



        <!-- origin -->
        <v-list-item-content
          class=" text-left ml-2"
        >
          <v-list-item-title
            class="text-city-size"
          >
            Nancy
          </v-list-item-title>
          <v-list-item-title
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            Boulevard d'Autrasie
          </v-list-item-title>
        </v-list-item-content>

        <!-- icon --> 
        <v-col
          cols="1"
        >
          <v-icon
            scolor="secondary"
            size="32"
          >
            mdi-ray-start-end
          </v-icon>
        </v-col> 

        <!-- destination --> 
        <v-list-item-content
          class=" text-left ml-1"
        >
          <v-list-item-title
            class="text-city-size"
          >
            Sanary-sur-Mer
          </v-list-item-title>
          <v-list-item-title
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            Rue d’Arcole
          </v-list-item-title>
        </v-list-item-content>

        <!--seats --> 
        <v-col
          align="center"
        >
          <p>3 places</p>
        </v-col>

        <!-- price --> 
        <v-col
          align="center"
          justify="end"
          class="price"
        >
          <p>50€</p>
        </v-col>
      </v-list-item>
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

  .price {
    font-size: 18px;
      font-weight: bold;
  }
</style>
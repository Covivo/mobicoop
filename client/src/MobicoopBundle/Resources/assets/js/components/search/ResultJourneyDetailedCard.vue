<template>
  <v-content>
    <v-container
      fluid
    >  
      <v-list-item>
        <!-- Icon driver--> 
        <v-list-item-avatar
          v-show="passenger == false"
          color="primary"
          size="60"
        >
          <v-icon
            dark
            size="35"
          >
            mdi-steering
          </v-icon>
        </v-list-item-avatar>

        <!-- Icon driver & passenger--> 
        <v-col           
          v-show="passenger == true && driver == true"
        >
          <v-list-item-avatar
            color="secondary"
            size="60"
          >
            <v-icon
              dark
              size="35"
            >
              mdi-account-supervisor 
            </v-icon>
          </v-list-item-avatar>

          <v-list-item-avatar
            color="primary"
            size="60"
          >
            <v-icon
              dark
              size="35"
            >
              mdi-steering
            </v-icon>
          </v-list-item-avatar>
        </v-col>

        <!-- Icon passenger--> 
          
        <v-list-item-avatar
          v-show="driver == false"
          color="secondary"
          size="60"
        >
          <v-icon
            dark
            size="35"
          >
            mdi-account-supervisor 
          </v-icon>
        </v-list-item-avatar>

        <!-- Hour & Date -->
        <v-list-item-content
          class="ml-2"
        >
          <v-list-item-title
            class="text-address-size"
          >
            <h3>{{ computedTimeFormated }}</h3>
          </v-list-item-title>
          <v-list-item-title class="date-uppercase">
            {{ computedDateFormated }}
          </v-list-item-title>
        </v-list-item-content>

        <!-- origin -->
        <v-list-item-content
          class=" text-left ml-2"
        >
          <v-list-item-title
            class="text-city-size"
          >
            {{ matching.waypoints[0].address.addressLocality }}
          </v-list-item-title>
          <v-list-item-title
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            {{ matching.waypoints[0].address.streetAddress }}
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
            {{ matching.waypoints[1].address.addressLocality }}
          </v-list-item-title>
          <v-list-item-title
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            {{ matching.waypoints[1].address.streetAddress }}
          </v-list-item-title>
        </v-list-item-content>

        <!--seats --> 
        <v-col
          align="center"
        >
          <p>
            <!-- place | places -->
            {{ matching.criteria.seats }} place(s)
          </p>
        </v-col>

        <!-- price --> 
        <v-col
          align="center"
          justify="end"
          class="price"
        >
          <p>{{ matching.criteria.priceKm }} €</p>
        </v-col>
        <v-row />
      </v-list-item>
    </v-container>
  </v-content>
</template>
<script>
import moment from "moment";
import 'moment/locale/fr';


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
    carpoolResults: {
      type: Object,
      default: null
    },
    matching: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      driver: this.matching.criteria.driver,
      passenger: this.matching.criteria.passenger,
    };
  },
  computed: {
    computedTimeFormated() {
      return moment(new Date(this.matching.criteria.fromTime)).utcOffset("+00:00").format("HH[h]mm")
    },
    computedDateFormated() {
      return moment(new Date(this.matching.criteria.fromDate)).utcOffset("+00:00").format("ddd DD/MM")
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

  .price {
    font-size: 18px;
      font-weight: bold;
  }
</style>
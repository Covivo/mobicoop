<template>
  <v-content>
    <v-container 
      fluid
    >
      <!-- it's the potential drivers (matchingRequests) - all offers containing drivers(proposalOffer) -->
      <v-list-item>
        <!-- Icon driver--> 
        <v-list-item-avatar
          v-if="driver === true && passenger === false"
          color="primary"
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

        <!-- Icon driver & passenger--> 
        <v-list-item           
          v-else-if="passenger === true && driver === true"
        >
          <v-list-item-avatar
            color="secondary"
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
            color="primary"
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
        </v-list-item>

        <!-- Icon passenger--> 
        <v-list-item           
          v-else-if="passenger === true && driver === false"
        >
          <v-list-item-avatar
            color="secondary"
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
        </v-list-item>
      
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
            {{ matching.proposalOffer.waypoints[0].address.addressLocality }}
          </v-list-item-title>
          <v-list-item-title
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            {{ matching.proposalOffer.waypoints[0].address.streetAddress }}
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
            {{ matching.proposalOffer.waypoints[1].address.addressLocality }}
          </v-list-item-title>
          <v-list-item-title
            class="d-inline-block text-truncate text-address-size"
            style="max-width: 100px;"
          >
            {{ matching.proposalOffer.waypoints[1].address.streetAddress }}
          </v-list-item-title>
        </v-list-item-content>

        <!--seats --> 
        <v-col
          align="center"
        >
          <p>
            <!-- place | places -->
            {{ matching.proposalOffer.criteria.seats }} place(s)
          </p>
        </v-col>

        <!-- price --> 
        <v-col
          align="center"
          justify="end"
          class="price"
        >
          <p>{{ matching.proposalOffer.criteria.priceKm }} €</p>
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
      dafault: null
    }
  },
  data() {
    return {
      driver: this.matching.proposalOffer.criteria.driver,
      passenger: this.matching.proposalOffer.criteria.passenger
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
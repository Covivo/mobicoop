<template>
  <v-content>
    <v-container
      fluid
    >
      <!-- ponctual -->
      <v-row
        v-if="matching.criteria.frequency == 2"
      >
        <v-col
          justify="right"
        >
          <v-chip
            small
            :color="matching.criteria.monCheck ? 'success' : 'default'"
          >
            L 
          </v-chip>
          <v-chip
            small
            :color="matching.criteria.tueCheck ? 'success' : 'default'"
          >
            Ma
          </v-chip>
          <v-chip
            small
            :color="matching.criteria.wedCheck ? 'success' : 'default'"
          >
            Me 
          </v-chip>
          <v-chip
            small
            :color="matching.criteria.thuCheck ? 'success' : 'default'"
          >
            J
          </v-chip>
          <v-chip
            small
            :color="matching.criteria.friCheck ? 'success' : 'default'"
          >
            V 
          </v-chip>
          <v-chip
            small
            :color="matching.criteria.satCheck ? 'success' : 'default'"
          >
            S
          </v-chip>
          <v-chip
            small
            :color="matching.criteria.sunCheck ? 'success' : 'default'"
          >
            D
          </v-chip>
        </v-col>
      </v-row>
      <v-list-item>
        <!-- Hour & Date -->
        <v-list-item-content
          class="ml-2"
        >
          <v-list-item-title
            v-if="matching.criteria.frequency == 1"
            class="text-address-size"
          >
            <h3>{{ computedTimeFormatedOccasional }}</h3>
          </v-list-item-title>
          <v-list-item-title
            v-else
          >
            <h3>{{ computedTimeFormatedRegular }}</h3>
          </v-list-item-title>
          <v-list-item-title 
            v-if="matching.criteria.frequency == 1"
          >
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
            color="secondary"
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
          v-if="passenger==false && driver ==true"
          align="center"
        >
          <p>
            <!-- place | places -->
            {{ matching.criteria.seats }} place(s)
          </p>
        </v-col>

        <!-- price --> 
        <v-col
          v-if="passenger==false && driver ==true"
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
    },
    regular: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      driver: this.matching.criteria.driver,
      passenger: this.matching.criteria.passenger,
    };
  },
  computed: {
    computedTimeFormatedOccasional() {
      return moment(new Date(this.matching.criteria.fromTime)).utcOffset("+00:00").format("HH[h]mm")
    },
    computedTimeFormatedRegular() {
      return moment(new Date(this.matching.criteria.monTime)).utcOffset("+00:00").format("HH[h]mm")
    },
    computedDateFormated() {
      return moment(new Date(this.matching.criteria.fromDate)).utcOffset("+00:00").format("ddd DD/MM")
    },
    computedSchedules(){
      this.schedules=this.matching.monTime
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
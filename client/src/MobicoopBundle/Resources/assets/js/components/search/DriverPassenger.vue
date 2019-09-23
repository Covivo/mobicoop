<template>
  <v-content>
    <v-container
      fluid
    >
      <v-list-item>
        <!-- Icon driver--> 
        <v-row
          align="center"
          justify="center"
        >
          <v-col
            v-show="driver == true && passenger == false"
          >
            <v-list-item
              align="center"
              justify="left"
              dense
            >
              <v-icon
                color="primary"
                size="75"
              >
                >
                mdi-steering
              </v-icon>
              {{ matching.user.givenName }} propose
            </v-list-item>
          </v-col>
        </v-row>

        <!-- Icon driver & passenger--> 
        <v-row
          align="center"
          justify="center"
        >
          <v-col
            v-show="driver == true && passenger == true"
          >
            <v-list-item
              align="center"
              justify="left"
              dense
            >
              <v-icon
                color="secondary"
                size="75"
              >
                >
                mdi-account-supervisor 
              </v-icon>
              <v-icon
                color="primary"
                size="75"
              >
                >
                mdi-steering
              </v-icon>
              peu  importe
            </v-list-item>
          </v-col>
        </v-row>
        <!-- Icon passenger--> 
        <v-row
          align="center"
          justify="center"
        >
          <v-col
            v-show="driver == false && passenger == true"
          >
            <v-list-item
              align="center"
              justify="left"
              dense
            >
              <v-icon
                color="secondary"
                size="75"
              >
                >
                mdi-account-supervisor 
              </v-icon>
              {{ matching.user.givenName }} cherche
            </v-list-item>
          </v-col>
        </v-row>
      </v-list-item>
    </v-container>
  </v-content>
</template>
<script>
import moment from "moment";
import 'moment/locale/fr';


export default {
  name: "DriverPassenger",
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
      passenger: this.matching.criteria.passenger
    };
  },
  computed: {
    computedTimeFormated() {
      return moment(new Date(this.matching.criteria.fromTime)).utcOffset("+00:00").format("HH[h]mm")
    },
    computedDateFormated() {
      return moment(new Date(this.matching.criteria.fromDate)).utcOffset("+00:00").format("ddd DD/MM")
    }
  },
}
</script>

<style scoped>
  .group {
display: flex;
flex: 1;
justify-content: space-around;
}
</style>
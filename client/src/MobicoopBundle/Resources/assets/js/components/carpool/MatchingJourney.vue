<template>
  <v-card>
    <v-card-title class="headline">
      {{ $t('detailTitle') }}
    </v-card-title>

    <v-card-text>
      <!-- Date / seats / price -->
      <v-row
        align="center"
        dense
      >
        <!-- Date -->
        <v-col
          v-if="!lRegular"
          cols="4"
          class="title text-center"
        >
          {{ computedDate }}
        </v-col>

        <v-col
          v-else
          cols="4"
          class="title text-center"
        >
          TODO
        </v-col>

        <!-- Seats -->
        <v-col
          cols="4"
          class="title text-center"
        >
          {{ $tc('places', proposal.criteria.seats, { seats: proposal.criteria.seats }) }}
        </v-col>

        <!-- Price -->
        <v-col
          cols="4"
          class="title text-center"
        >
          {{ computedPrice ? computedPrice +'€' : '' }}
        </v-col>
      </v-row>

      <!-- Route / carpooler -->
      <v-row
        align="center"
        dense
      > 
        <v-col
          cols="8"
        >
          <v-journey
            :time="time"
            :waypoints="waypoints"
          />
        </v-col>
        <v-col
          cols="4"
        >
          Carpooler
        </v-col>
      </v-row>
    </v-card-text>

    <v-card-actions>
      <div class="flex-grow-1" />

      <v-btn
        v-if="driver ^ passenger"
        color="success"
        @click="carpoolDialog = false"
      >
        {{ $t('carpool') }}
      </v-btn>

      <v-btn
        v-if="driver && passenger"
        color="success"
        @click="carpoolDialog = false"
      >
        {{ $t('carpoolAsDriver') }}
      </v-btn>

      <v-btn
        v-if="driver && passenger"
        color="success"
        @click="carpoolDialog = false"
      >
        {{ $t('carpoolAsPassenger') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/MatchingJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/MatchingJourney.json";
import VJourney from "../utilities/VJourney";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    VJourney
  },
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
    origin: {
      type: Object,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    matching: {
      type: Object,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    user: {
      type:Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    }
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      lOrigin: this.origin,
      lDestination: this.destination,
      lMatching: this.matching,
      lDate: this.date,
      lUser: this.user,
      lRegular: this.regular,
      time: "08:00"
    }
  },
  computed: {
    driver() {
      // the matching user is driver if he has an offer
      return this.lMatching.offer ? true : false
    },
    passenger() {
      // the matching user is driver if he has a request
      return this.lMatching.request ? true : false
    },
    computedDate() {
      if (this.lRegular) {
        return moment.utc(this.driver ? this.lMatching.offer.criteria.fromDate : this.lMatching.request.criteria.fromDate).format(this.$t("ui.i18n.date.format.fullDate"))
      }
      return this.proposal.criteria.fromDate
        ? moment.utc(this.proposal.criteria.fromDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : ""; 
    },
    computedPrice() {
      return this.driver ? Math.round((this.lMatching.offer.proposalOffer.criteria.priceKm*this.lMatching.offer.proposalRequest.criteria.directionPassenger.distance/1000)*100)/100 : null
    },
    proposal() {
      return this.lMatching.offer ? this.lMatching.offer.proposalOffer : this.lMatching.request.proposalRequest;
    }, 
    waypoints() {
      return this.computeWaypoints();
    }
  },
  watch: {
    origin(val) {
      this.lOrigin = val;
    },
    destination(val) {
      this.lDestination = val;
    },
    matching(val) {
      this.lMatching = val;
    },
    date(val) {
      this.lDate = val;
    },
    user(val) {
      this.lUser = val;
    },
    regular(val) {
      this.lRegular = val;
    }
  },
  methods: {
    computeWaypoints() {
      let waypoints = [];
      let isDriver = this.driver;
      let thisOrigin = this.origin;
      let thisDestination = this.destination;
      let order = this.driver ? this.lMatching.offer.filters.order : this.lMatching.request.filters.order;
      order.forEach(function (waypoint, index) {
        waypoints.push({
          id: index,
          address: 
            isDriver ? 
              (waypoint.candidate == 1 ? waypoint.address : (waypoint.position == '0' ? thisOrigin : thisDestination)) :
              (waypoint.candidate == 1 ? (waypoint.position == '0' ? thisOrigin : thisDestination) : waypoint.address),
          duration: waypoint.duration,
          level: "primary"
        });
      });
      return waypoints;
    },
    // this methods gives the date of the carpool
    getCarpoolDate(params) {
      // if the search is regular, it's the selected date
      if (this.lRegular) return this.lDate;
      // if the search is punctual
      if (this.proposal.criteria.frequency == 1) {
        // it's the date of the matching proposal if the matching proposal is punctual
        return params.proposal.criteria.fromDate;
      } 
      // TODO : it's the date of the first matching date if it's a regular matching proposal
      // for now we return the selected date...
      return this.date;
    },
    // this methods gives the time of the carpool
    getCarpoolTime(params) {
      // if the search is regular, the time is null
      if (this.lRegular) return null;
      // the search is punctual
      if (this.driver && !this.passenger) {
        // if the requester is passenger only, we have to set its departure time as the pickup time 
        // the 'order' field is an array containing the ordered waypoints of the whole route (ACDB)
        return this.getPickUpTime(1,params.proposal.criteria.fromTime,this.matching.offer.filters.order);
      } else if (this.passenger && !this.driver) {
        // if the requester is driver only, 
        // we set its departure time to the time where the pickup time of the current carpooler matches the carpooler departure time
        // the 'order' field is an array containing the ordered waypoints of the whole route (ACDB)
        return this.getPickUpTime(2,params.proposal.criteria.fromTime,this.matching.request.filters.order);
      } else {
        // if the requester can be driver or passenger, we choose a departure time that could satisfy both roles 
        // the 'order' field is an array containing the ordered waypoints of the whole route (ACDB)
        return this.getPickUpTime(3,params.proposal.criteria.fromTime,this.matching.offer.filters.order);
      }
    },
    getPickUpTime(role,time,waypoints) {
      // we search the pickup point
      // the driver is the candidate 1 in the waypoints
      // the passenger is the candidate 2 in the waypoints
      // => the pickup is in position 0 for the candidate 2
      // => if the role is 1 (the carpooler is driver = candidate 1) the pickup time is the carpooler time + the duration till the pickup of the requester
      // => if the role is 2 (the carpooler is passenger = candidate 2) the pickup time is the carpooler time - the duration till the pickup of the carpooler
      // => if the role is 3 (the carpooler can be driver or passenger, we use the driver role for calculation = candidate 1) 
      //    the pickup time is the carpooler time - half the duration till the pickup of the requester
      //    (arbitrary evaluation that could satisfy both roles, as the carpooler is the first who have posted we try to satisfy him first !)
      for (let waypoint of waypoints) {
        if (waypoint.candidate == 2 && waypoint.position == '0') {
          if (role == 1) {
            return moment.utc(time).add(waypoint.duration,'seconds').format(this.$t("ui.i18n.time.format.hourMinute"));
          } else if (role == 2) {
            return moment.utc(time).subtract(waypoint.duration,'seconds').format(this.$t("ui.i18n.time.format.hourMinute"));
          } else {
            return moment.utc(time).subtract((waypoint.duration)/2,'seconds').format(this.$t("ui.i18n.time.format.hourMinute"));
          }
        }
      }
      return null;
    },
  }
};
</script>
<style>
</style>
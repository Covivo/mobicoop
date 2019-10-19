<template>
  <v-card>
    <v-toolbar
      color="primary"
    >
      <v-toolbar-title>
        {{ $t('detailTitle') }}
      </v-toolbar-title>
      
      <v-spacer />

      <v-btn 
        icon
        @click="$emit('close')"
      >
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-toolbar>

    <v-card-text>
      <!-- Date / seats / price -->
      <v-row
        align="center"
        dense
      >
        <!-- Date -->
        <v-col
          v-if="!lRegular"
          cols="5"
          class="title text-center"
        >
          {{ computedDate }}
        </v-col>

        <v-col
          v-else
          cols="5"
          class="title text-center"
        >
          <regular-days-summary 
            :mon-active="monActive"
            :tue-active="tueActive"
            :wed-active="wedActive"
            :thu-active="thuActive"
            :fri-active="friActive"
            :sat-active="satActive"
            :sun-active="sunActive"
          />
        </v-col>

        <!-- Seats -->
        <v-col
          cols="3"
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
        <!-- Route -->
        <v-col
          cols="8"
        >
          <v-row>
            <v-col>
              <v-journey
                :time="computedTime"
                :waypoints="waypoints"
              />
            </v-col>
          </v-row>
          <v-row 
            v-if="proposal.comment"
          >
            <v-col>
              <v-card
                outlined
                class="mx-auto"
              > 
                <v-card-text class="pre-formatted">
                  {{ proposal.comment }}
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-col>

        <!-- Carpooler -->
        <v-col
          cols="4"
        >
          <v-card>
            <!-- Avatar -->
            <v-img
              aspect-ratio="2"
              src="https://avataaars.io/?avatarStyle=Transparent&topType=ShortHairShortRound&accessoriesType=Blank&hairColor=BrownDark&facialHairType=Blank&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Default&skinColor=Light"
            />
            <v-card-title>
              <v-row
                dense
              >
                <v-col
                  class="text-center"
                >
                  {{ proposal.user.givenName }} {{ proposal.user.familyName.substr(0,1).toUpperCase()+"." }}
                </v-col>
              </v-row>
            </v-card-title>
            <v-card-text>
              <v-row
                dense
              >
                <v-col
                  cols="12"
                  class="text-center"
                >
                  {{ age }}
                </v-col>
                <v-col
                  cols="12"
                  class="text-center"
                >
                  {{ proposal.user.telephone }}
                </v-col>
                
                <v-col  
                  cols="12"
                  class="text-center"
                >
                  <v-btn
                    color="primary"
                    :loading="contactLoading"
                    @click="contact"
                  >
                    <v-icon>
                      mdi-email
                    </v-icon>
                    {{ $t('contact') }}
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-card-text>

    <!-- Action buttons -->
    <v-card-actions>
      <div class="flex-grow-1" />

      <v-btn
        v-if="driver ^ passenger"
        color="secondary"
        @click="carpoolDialog = false"
      >
        {{ $t('carpool') }}
      </v-btn>

      <v-btn
        v-if="driver && passenger"
        color="secondary"
        @click="carpoolDialog = false"
      >
        {{ $t('carpoolAsDriver') }}
      </v-btn>

      <v-btn
        v-if="driver && passenger"
        color="secondary"
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
import Translations from "@translations/components/carpool/results/MatchingJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/MatchingJourney.json";
import VJourney from "@components/carpool/utilities/VJourney";
import RegularDaysSummary from "@components/carpool/utilities/RegularDaysSummary";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    VJourney,
    RegularDaysSummary
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
      contactLoading: false
    }
  },
  computed: {
    age (){
      return moment().diff(moment([this.proposal.user.birthYear]),'years')+' '+this.$t("birthYears")
    },
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
        // regular search => fromDate
        return moment.utc(this.driver ? this.lMatching.offer.criteria.fromDate : this.lMatching.request.criteria.fromDate).format(this.$t("ui.i18n.date.format.fullDate"))
      }
      if (this.proposal.criteria.frequency == 2) {
        // punctual search && regular result => first matching date
        let searchDate = this.lDate ? this.lDate : new Date();
        if (moment.utc(searchDate).isSameOrAfter(this.proposal.criteria.fromDate)) {
          // the search date is >= fromDate of the proposal => the search date is ok
          return moment.utc(searchDate).format(this.$t("ui.i18n.date.format.fullDate"));
        }
        // the fromDate is after the search date, we take fromDate
        return moment.utc(this.proposal.criteria.fromDate).format(this.$t("ui.i18n.date.format.fullDate"));
      }
      return this.proposal.criteria.fromDate
        ? moment.utc(this.proposal.criteria.fromDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : ""; 
    },
    computedPrice() {
      return this.driver ? Math.round((this.lMatching.offer.proposalOffer.criteria.priceKm*this.lMatching.offer.proposalRequest.criteria.directionPassenger.distance/1000)*100)/100 : null
    },
    computedTime() {
      if (this.proposal.criteria.frequency == 2) {
        if (this.lRegular) {
          return null;
        }
        // we have to search the week day and display the time
        const dayOfWeek = moment.utc(this.proposal.criteria.fromDate).format('d');
        switch (dayOfWeek) {
        case '0' : 
          return moment.utc(this.proposal.criteria.sunTime).format(this.$t("ui.i18n.time.format.hourMinute"));
        case '1' : 
          return moment.utc(this.proposal.criteria.monTime).format(this.$t("ui.i18n.time.format.hourMinute"));
        case '2' : 
          return moment.utc(this.proposal.criteria.tueTime).format(this.$t("ui.i18n.time.format.hourMinute"));
        case '3' : 
          return moment.utc(this.proposal.criteria.wedTime).format(this.$t("ui.i18n.time.format.hourMinute"));
        case '4' : 
          return moment.utc(this.proposal.criteria.thuTime).format(this.$t("ui.i18n.time.format.hourMinute"));
        case '5' : 
          return moment.utc(this.proposal.criteria.friTime).format(this.$t("ui.i18n.time.format.hourMinute"));
        case '6' : 
          return moment.utc(this.proposal.criteria.satTime).format(this.$t("ui.i18n.time.format.hourMinute"));
        default:
          return '';
        }
      } else {
        return this.proposal.criteria.fromTime
          ? moment.utc(this.proposal.criteria.fromTime).format(this.$t("ui.i18n.time.format.hourMinute"))
          : ""; 
      }
    },
    proposal() {
      return this.lMatching.offer ? this.lMatching.offer.proposalOffer : this.lMatching.request.proposalRequest;
    }, 
    waypoints() {
      return this.computeWaypoints();
    },
    monActive() {
      return (this.proposal.criteria.monCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.moncheck));
    },
    tueActive() {
      return  (this.proposal.criteria.tueCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.tueCheck));
    },
    wedActive() {
      return  (this.proposal.criteria.wedCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.wedCheck));
    },
    thuActive() {
      return  (this.proposal.criteria.thuCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.thuCheck));
    },
    friActive() {
      return  (this.proposal.criteria.friCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.friCheck));
    },
    satActive() {
      return  (this.proposal.criteria.satCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.satCheck));
    },
    sunActive() {
      return  (this.proposal.criteria.sunCheck || (this.proposal.proposalLinked && this.proposal.proposalLinked.criteria.sunCheck));
    },
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
      let destinationId = order.length-1;
      order.forEach(function (waypoint, index) {
        waypoints.push({
          id: index,
          // requester is a boolean, 
          // it is set to true if the waypoint is a waypoint created by the requester (the person who make the request)
          requester: 
            isDriver ? 
              (waypoint.candidate == 1 ? false : true) :
              (waypoint.candidate == 1 ? true : false),
          address: 
            isDriver ? 
              (waypoint.candidate == 1 ? waypoint.address : (waypoint.position == '0' ? thisOrigin : thisDestination)) :
              (waypoint.candidate == 1 ? (waypoint.position == '0' ? thisOrigin : thisDestination) : waypoint.address),
          duration: waypoint.duration,
          icon: (waypoint.candidate == 1 ? (waypoint.position == '0' ? "mdi-home" : (index == destinationId ? "mdi-flag-checkered" : "mdi-debug-step-into")) : (waypoint.position == '0' ? "mdi-human-greeting" : "mdi-flag"))
        });
      });
      return waypoints;
    },
    contact() {
      this.contactLoading = true;
      this.$emit('contact', {
        proposal: this.proposal,
        date: this.lDate,
        time: this.getCarpoolTime(),
        driver: this.driver,
        passenger: this.passenger
      });
    },


    // the following is not used for now but is to keep for further use !!! 

    // this methods gives the date of the carpool
    /*getCarpoolDate(params) {
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
    },*/
    // this methods gives the time of the carpool
    getCarpoolTime() {
      // if the search is regular, the time is null
      if (this.lRegular) return null;
      // the search is punctual
      if (this.driver && !this.passenger) {
        // if the requester is passenger only, we have to set its departure time as the pickup time 
        // the 'order' field is an array containing the ordered waypoints of the whole route (ACDB)
        return this.getPickUpTime(1,this.proposal.criteria.fromTime,this.matching.offer.filters.order);
      } else if (this.passenger && !this.driver) {
        // if the requester is driver only, 
        // we set its departure time to the time where the pickup time of the current carpooler matches the carpooler departure time
        // the 'order' field is an array containing the ordered waypoints of the whole route (ACDB)
        return this.getPickUpTime(2,this.proposal.criteria.fromTime,this.matching.request.filters.order);
      } else {
        // if the requester can be driver or passenger, we choose a departure time that could satisfy both roles 
        // the 'order' field is an array containing the ordered waypoints of the whole route (ACDB)
        return this.getPickUpTime(3,this.proposal.criteria.fromTime,this.matching.offer.filters.order);
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
            return moment.utc(time).add(waypoint.duration,'seconds').format('HH:mm');
          } else if (role == 2) {
            return moment.utc(time).subtract(waypoint.duration,'seconds').format('HH:mm');
          } else {
            return moment.utc(time).subtract((waypoint.duration)/2,'seconds').format('HH:mm');
          }
        }
      }
      return null;
    }
  }
};
</script>
<style>
</style>
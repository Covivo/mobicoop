<template>
  <div>
    <v-row
      align="center"
      dense
    >
      <!-- Time -->
      <v-col
        v-if="proposal.criteria.frequency==1 || !regular"
        cols="3"
      >
        <v-list-item two-line>
          <v-list-item-content>
            <v-list-item-title class="title font-weight-bold">
              {{ computedTime }}
            </v-list-item-title>
            <v-list-item-subtitle class="subtitle-1">
              {{ computedDate }}
            </v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-col>
      <!-- Route -->
      <v-col
        :cols="regular ? '9' : '6'"
      >
        <route-summary
          :origin="computedOrigin"
          :destination="computedDestination"
          :type="2"
          :regular="regular"
        />
      </v-col>
      <!-- Seats -->
      <v-col
        cols="2"
      >
        {{ $tc('places', proposal.criteria.seats, { seats: proposal.criteria.seats }) }}
      </v-col>
      <!-- Price -->
      <v-col
        cols="1"
        class="title"
      >
        {{ computedPrice ? computedPrice +'€' : '' }}
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/utilities/JourneySummary.json";
import TranslationsClient from "@clientTranslations/components/utilities/JourneySummary.json";
import RouteSummary from "../utilities/RouteSummary"

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    RouteSummary
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    matching: {
      type: Object,
      default: null
    },
    user: {
      type:Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    date: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
      proposal: this.matching.offer ? this.matching.offer.proposalOffer : this.matching.request.proposalRequest
    };
  },
  computed: {
    driver() {
      // the matching user is driver if he has an offer
      return this.matching.offer ? true : false
    },
    passenger() {
      // the matching user is driver if he has a request
      return this.matching.request ? true : false
    },
    computedOrigin() {
      return {
        streetAddress: this.proposal.waypoints[0].address.streetAddress,
        addressLocality: this.proposal.waypoints[0].address.addressLocality
      }
    },
    computedDestination() {
      return {
        streetAddress: this.proposal.waypoints[this.proposal.waypoints.length-1].address.streetAddress,
        addressLocality: this.proposal.waypoints[this.proposal.waypoints.length-1].address.addressLocality,
      }
    },
    computedTime() {
      if (this.proposal.criteria.frequency == 2) {
        // we have to search the week day and display the time
        const dayOfWeek = moment.utc(this.date).format('d');
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
    computedDate() {
      if (this.proposal.criteria.frequency == 2) {
        return moment.utc(this.driver ? this.matching.offer.criteria.fromDate : this.matching.request.criteria.fromDate).format(this.$t("ui.i18n.date.format.shortDate"))
      }
      return this.proposal.criteria.fromDate
        ? moment.utc(this.proposal.criteria.fromDate).format(this.$t("ui.i18n.date.format.shortDate"))
        : ""; 
    },
    computedPrice() {
      return this.driver ? Math.round((this.matching.offer.proposalOffer.criteria.priceKm*this.matching.offer.proposalRequest.criteria.directionPassenger.distance/1000)*100)/100 : null
    }
  },
  methods: {
  }
};
</script>
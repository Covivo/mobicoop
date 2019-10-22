<template>
  <div>
    <v-row
      align="center"
      dense
    >
      <!-- Time -->
      <v-col
        v-if="!regular"
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
        {{ $tc('places', result.seats, { seats: result.seats }) }}
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
import Translations from "@translations/components/carpool/utilities/JourneySummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/JourneySummary.json";
import RouteSummary from "@components/carpool/utilities/RouteSummary"

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    RouteSummary
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    result: {
      type: Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    user: {
      type:Object,
      default: null
    }
  },
  data() {
    return {
      locale: this.$i18n.locale
    };
  },
  computed: {
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
      return moment.utc(this.result.time).format(this.$t("ui.i18n.time.format.hourMinute"));      
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
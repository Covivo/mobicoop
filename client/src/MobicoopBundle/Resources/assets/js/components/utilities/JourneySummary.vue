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
import CommonTranslations from "@translations/translations.json";
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
    sharedMessages: CommonTranslations
  },
  props: {
    driver: {
      type: Boolean,
      default: false
    },
    passenger: {
      type: Boolean,
      default: false
    },
    proposal: {
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
  },
  data() {
    return {
      locale: this.$i18n.locale,
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
        streetAddress: this.proposal.waypoints[1].address.streetAddress,
        addressLocality: this.proposal.waypoints[1].address.addressLocality,
      }
    },
    computedTime() {
      moment.locale(this.locale);
      if (!this.regular) {
        return this.proposal.criteria.fromTime
          ? moment(this.proposal.criteria.fromTime).format(this.$t("ui.i18n.time.format.hourMinute"))
          : ""; 
      }
      return "";
    },
    computedDate() {
      moment.locale(this.locale);
      if (!this.regular) {
        return this.proposal.criteria.fromDate
          ? moment(this.proposal.criteria.fromDate).format(this.$t("ui.i18n.date.format.shortDate"))
          : ""; 
      }
      return "";
    },
    computedPrice() {
      return this.driver ? Math.round((this.proposal.criteria.priceKm*this.proposal.criteria.directionDriver.distance/1000)*100)/100 : null
    }
  },
  methods: {
  }
};
</script>
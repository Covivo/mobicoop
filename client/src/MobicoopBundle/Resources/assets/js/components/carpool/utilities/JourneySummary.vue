<template>
  <div>
    <v-row
      align="center"
      dense
    >
      <v-col
        v-if="date"
        cols="2"
      >
        <v-list-item two-line>
          <v-list-item-content>
            <!-- Pickup -->
            <v-list-item-title
              v-if="!hidePickUp"
              class="text-body-2"
            >
              {{ pickupDisplay }}
            </v-list-item-title>
            <!-- Date and time -->
            <v-list-item-title class="text-h6 font-weight-bold">
              {{ computedTime }}
            </v-list-item-title>
            <v-list-item-subtitle class="text-body-2">
              {{ computedDate }}
            </v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-col>
      <!-- Route -->
      <v-col
        :cols="!date ? '8' : '6'"
      >
        <route-summary
          :origin="origin"
          :origin-first="originFirst"
          :destination="destination"
          :destination-last="destinationLast"
          :type="2"
          :regular="!date"
        />
      </v-col>
      <!-- Seats -->
      <v-col
        cols="2"
        class="text-right"
      >
        {{ $tc('places', seats, { seats: seats }) }}
      </v-col>
      <!-- Price -->
      <v-col
        cols="2"
        class="text-h6 text-right"
        :class="solidaryExclusive ? 'warning--text' : ''"
      >
        {{ price ? price +'€' : '' }}
        <v-tooltip
          slot="append"
          right
          :color="solidaryExclusive ? 'warning' : 'info'"
          :max-width="'35%'"
        >
          <template v-slot:activator="{ on }">
            <v-icon
              justify="left"
              :color="solidaryExclusive ? 'warning' : 'default'"
              v-on="on"
            >
              mdi-help-circle-outline
            </v-icon>
          </template>
          <span v-if="solidaryExclusive">
            {{ $t('priceTooltipSolidaryExclusive') }}
          </span>
          <span v-else>
            {{ $t('priceTooltip') }}
          </span>
        </v-tooltip>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/utilities/JourneySummary/";
import RouteSummary from "@components/carpool/utilities/RouteSummary"

export default {
  components: {
    RouteSummary
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
    pickUp: {
      type: Object,
      default: null
    },
    originFirst: {
      type: Boolean,
      default: false
    },
    destinationLast: {
      type: Boolean,
      default: false
    },
    date: {
      type: String,
      default: null
    }, 
    time: {
      type: String,
      default: null
    }, 
    price: {
      type: String,
      default: null
    },  
    seats: {
      type: Number,
      default: null
    },
    solidaryExclusive: {
      type: Boolean,
      default: false
    },
    hidePickUp: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE")
    };
  },
  computed: {
    computedTime() {
      if (this.time) {
        return moment.utc(this.time).format(this.$t("hourMinute"));  
      }
      return null;  
    },
    computedDate() {
      if (this.date) {
        return moment.utc(this.date).format(this.$t("shortDate"));
      }
      return null;
    },
    pickupDisplay() {
      return (this.pickUp.addressLocality) ? this.pickUp.addressLocality : '';
    },
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
  }
};
</script>

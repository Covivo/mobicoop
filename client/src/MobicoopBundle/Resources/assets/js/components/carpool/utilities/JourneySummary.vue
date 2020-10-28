<template>
  <div>
    <v-row
      align="center"
      dense
    >
      <!-- Date and time -->
      <v-col
        v-if="date"
        cols="2"
      >
        <v-list-item two-line>
          <v-list-item-content>
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
import { merge } from "lodash";
import moment from "moment";
import {messages_en, messages_fr} from "@translations/components/carpool/utilities/JourneySummary/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/carpool/utilities/JourneySummary/";
import RouteSummary from "@components/carpool/utilities/RouteSummary"

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  components: {
    RouteSummary
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
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
    }  
  },
  data() {
    return {
      locale: this.$i18n.locale
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
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
  }
};
</script>

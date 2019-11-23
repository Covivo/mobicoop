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
        class="text-center"
      >
        {{ $tc('places', seats, { seats: seats }) }}
      </v-col>
      <!-- Price -->
      <v-col
        cols="2"
        class="title text-right"
      >
        {{ price ? price +'€' : '' }}
        <v-tooltip
          slot="append"
          right
          color="info"
          :max-width="'35%'"
        >
          <template v-slot:activator="{ on }">
            <v-icon
              justify="left"
              v-on="on"
            >
              mdi-help-circle-outline
            </v-icon>
          </template>
          <span>{{ $t('priceTooltip') }}</span>
        </v-tooltip>
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
  },
  data() {
    return {
      locale: this.$i18n.locale
    };
  },
  computed: {
    computedTime() {
      if (this.time) {
        return moment.utc(this.time).format(this.$t("ui.i18n.time.format.hourMinute"));  
      }
      return null;  
    },
    computedDate() {
      if (this.date) {
        return moment.utc(this.date).format(this.$t("ui.i18n.date.format.shortDate"));
      }
      return null;
    }
  },
  methods: {
  }
};
</script>
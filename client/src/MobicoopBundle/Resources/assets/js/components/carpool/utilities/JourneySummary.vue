<template>
  <div>
    <v-row
      align="center"
      dense
    >
      <!-- Date and time -->
      <v-col
        v-if="date"
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
        :cols="!date ? '9' : '6'"
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
      >
        {{ $tc('places', seats, { seats: seats }) }}
      </v-col>
      <!-- Price -->
      <v-col
        cols="1"
        class="title"
      >
        {{ price ? price +'€' : '' }}
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
      return moment.utc(this.time).format(this.$t("ui.i18n.time.format.hourMinute"));      
    },
    computedDate() {
      return moment.utc(this.date).format(this.$t("ui.i18n.date.format.shortDate"))
    }
  },
  methods: {
  }
};
</script>
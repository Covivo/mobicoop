<template>
  <div>
    <v-container>
      <v-row
        v-for="(time,index) in days"
        :key="index"
        dense
        align="center"
      >
        <!-- First col : outward / return / days -->
        <v-col
          cols="4"
        >
          {{ index }} {{ time }}
        </v-col>
        <!-- Second col : fromDate / switches -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="test"
            color="success"
            inset
            dense
          />
        </v-col>
        <!-- Third col : slider / date ranges -->
        <v-col
          cols="6"
        >
          jours
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/carpool/utilities/RegularAsk.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/RegularAsk.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    type: {
      type: Number,
      default: 1
    },
    origin: {
      type: Object,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    fromDate: {
      type: String,
      default: null
    },
    maxDate: {
      type: String,
      default: null
    },
    days: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      locale: this.$i18n.locale
    };
  },
  computed: {
    computedStartDateFormat() {
      moment.locale(this.locale);
      return this.startDate
        ? moment(this.startDate).format(this.$t("i18n.date.format.fullDate"))
        : "";
    },
  },
  methods: {
    clearStartDate() {
      this.startDate = null;
    },
  }
};
</script>
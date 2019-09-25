<template>
  <v-container fluid>
    <!--Route row-->
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="10"
      >
        <route-summary
          :origin="computedOrigin"
          :destination="computedDestination"
          :regular="regular"
          :type="1"
        />
      </v-col>

      <!-- Modify icon -->
      <v-col
        cols="2"
      >
        <v-btn
          color="primary"
          fab
          small
          depressed
        >
          <v-icon
            @click="buttonAlert('en cours de développement',$event);"
          >
            mdi-lead-pencil
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <!-- date row-->
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="12"
        align="left"
        class="subtitle-1"
      >
        {{ computedDateFormat }}
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/MatchingHeader.json";
import TranslationsClient from "@clientTranslations/components/carpool/MatchingHeader.json";
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
    origin: {
      type: String,
      default: null
    },
    destination: {
      type: String,
      default: null
    },
    date: {
      type: String,
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
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    computedOrigin() {
      return {
        streetAddress: 'rue de la monnaie',
        addressLocality: this.origin
      }
    },
    computedDestination() {
      return {
        streetAddress: 'rue de la cheneau',
        addressLocality: this.destination
      }
    },
  },
  methods: {
    buttonAlert(msg, e) {
      alert(msg);
    },
  }
};
</script>
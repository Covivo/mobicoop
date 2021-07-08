<template>
  <div>
    <!--Route row-->
    <v-row
      align="center"
      dense
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
      <!-- <v-col
        cols="2"
        class="text-right"
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
      </v-col> -->
    </v-row>

    <!-- date row-->
    <v-row
      dense
    >
      <v-col
        cols="5"
        align="left"
        class="ml-4 mt-0 text-subtitle-1 font-weight-bold"
      >
        {{ computedDateFormat }}
      </v-col>
    </v-row>
  </div>
</template>

<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/results/MatchingHeader/";
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
    date: {
      type: String,
      default: null
    },
    time: {
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
      locale: localStorage.getItem("X-LOCALE"),
    };
  },
  computed: {
    computedDateFormat() {
      return this.date
        ? moment(this.date).format(this.$t("fullDate"))
        : "";
    },
    computedOrigin() {
      return {
        streetAddress: this.origin.streetAddress,
        addressLocality: this.origin.addressLocality
      }
    },
    computedDestination() {
      return {
        streetAddress: this.destination.streetAddress,
        addressLocality: this.destination.addressLocality
      }
    },
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    buttonAlert(msg, e) {
      alert(msg);
    },
  }
};
</script>
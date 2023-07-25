<template>
  <v-timeline
    dense
  >
    <v-timeline-item
      color="secondary"
      fill-dot
    >
      <template v-slot:icon>
        <v-avatar>
          <v-icon
            color="white"
            v-on="on"
          >
            mdi-human-greeting
          </v-icon>
        </v-avatar>
      </template>
      <v-row dense>
        <v-col
          cols="12"
          class="text-left"
        >
          <span :class="'font-weight-bold'">{{ booking.passengerPickupAddress }}</span>
        </v-col>
      </v-row>
    </v-timeline-item>
    <v-timeline-item
      color="secondary"
      fill-dot
    >
      <template v-slot:icon>
        <v-avatar>
          <v-icon
            color="white"
          >
            mdi-flag
          </v-icon>
        </v-avatar>
      </template>
      <v-row dense>
        <v-col
          cols="12"
          class="text-left"
        >
          <span :class="'font-weight-bold'">{{ booking.passengerDropAddress }}</span>
        </v-col>
      </v-row>
    </v-timeline-item>
  </v-timeline>
</template>

<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/utilities/VJourney/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  components: {
  },
  props: {
    booking: {
      type: Object,
      default: null
    },
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      message:null
    };
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  }
};
</script>

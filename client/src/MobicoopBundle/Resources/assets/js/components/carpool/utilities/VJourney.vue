<template>
  <v-timeline
    dense
  >
    <v-timeline-item 
      v-for="waypoint in waypoints"
      :key="waypoint.id"
      small
    >
      <v-row dense>
        <v-col 
          v-if="time"
          cols="2"
        >
          <strong>{{ getTime(waypoint.duration) }}</strong>
        </v-col>
        <v-col 
          :cols="time ? '10' : '12'"
        >
          <strong>{{ waypoint.address.addressLocality }}</strong> {{ waypoint.address.venue ? ' - ' + waypoint.address.venue : waypoint.address.streetAddress ? ' - ' + waypoint.address.streetAddress : null }}
        </v-col>
      </v-row>
    </v-timeline-item>
  </v-timeline>
</template>

<script>
import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/utilities/VJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/VJourney.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
  },
  props: {
    time: {
      type: String,
      default: null
    },
    waypoints: {
      type: Array,
      default: null
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
    };
  },
  methods: {
    getTime(duration) {
      return moment.utc(this.time,"HH:mm").add(duration,'seconds').format(this.$t("ui.i18n.time.format.hourMinute")); 
    }
  }
};
</script>
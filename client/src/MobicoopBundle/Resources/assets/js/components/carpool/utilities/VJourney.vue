<template>
  <v-timeline
    dense
  >
    <v-timeline-item 
      v-for="waypoint in waypoints"
      :key="waypoint.id"
      :color="waypoint.person == 'requester' ? 'primary' : 'secondary'"
      :icon="getIcon(waypoint.type,waypoint.role)"
      fill-dot
    >
      <v-row dense>
        <v-col 
          v-if="time"
          cols="2"
        >
          <strong>{{ formatTime(waypoint.time) }}</strong>
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
import Translations from "@translations/components/carpool/utilities/VJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/VJourney.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  components: {
  },
  props: {
    time: {
      type: Boolean,
      default: false
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
  //icon:
        
  methods: {
    getIcon(type,role) {
      if (role == 'driver') {
        if (type == 'origin') return 'mdi-home';
        if (type == 'destination') return 'mdi-flag-checkered';
        if (type == 'step') return 'mdi-debug-step-into';
      } else {
        if (type == 'origin') return 'mdi-human-greeting';
        if (type == 'destination') return 'mdi-flag';
        if (type == 'step') return 'mdi-debug-step-into';
      }
    },
    formatTime(time) {
      return moment.utc(time,"HH:mm").format(this.$t("ui.i18n.time.format.hourMinute")); 
    }
  }
};
</script>
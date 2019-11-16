<template>
  <v-timeline
    dense
  >
    <v-timeline-item 
      v-for="waypoint in waypoints"
      :key="waypoint.id"
      :color="waypoint.person == 'requester' ? 'primary' : 'secondary'"
      fill-dot
    >
      <template v-slot:icon>
        <v-avatar>
          <img
            v-if="waypoint.avatar && waypoint.avatar!==''"
            :src="waypoint.avatar"
          >
          <v-icon v-else>
            {{ getIcon(waypoint.type,waypoint.role) }}
          </v-icon>
        </v-avatar>
      </template>    
      <v-row dense>
        <v-col 
          v-if="time"
          cols="3"
          class="text-left"
        >
          <span :class="role == waypoint.role ? 'font-weight-bold' : ''">{{ formatTime(waypoint.time) }}</span>
        </v-col>
        <v-col 
          :cols="time ? '9' : '12'"
          class="text-left"
        >
          <v-icon>{{ getIcon(waypoint.type,waypoint.role) }}</v-icon>
          <span :class="role == waypoint.role ? 'font-weight-bold' : ''">{{ waypoint.address.addressLocality }}</span> {{ waypoint.address.venue ? ' - ' + waypoint.address.venue : waypoint.address.streetAddress ? ' - ' + waypoint.address.streetAddress : null }}
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
    },
    role: {
      type: String,
      default: null
    },
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
      return moment.utc(time).format(this.$t("ui.i18n.time.format.hourMinute")); 
    }
  }
};
</script>
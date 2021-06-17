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
        <v-tooltip
          color="info" 
          right
        >
          <template v-slot:activator="{ on }">
            <v-avatar>
              <img
                v-if="waypoint.avatar && waypoint.avatar!==''"
                :src="waypoint.avatar"
              >
              <v-icon
                v-else
                color="white"
                v-on="on"
              >
                {{ getIcon(waypoint.type,waypoint.role) }}
              </v-icon>
            </v-avatar>
          </template>
          <span> {{ getTooltipMessage (waypoint.type,waypoint.role) }}</span>
        </v-tooltip>
      </template>    
      <v-row dense>
        <v-col 
          v-if="time && waypoint.time"
          cols="3"
          class="text-left"
        >
          <span :class="'passenger' == waypoint.role ? 'font-weight-bold' : ''">{{ formatTime(waypoint.time) }}</span>
        </v-col>
        <v-col 
          :cols="time ? '9' : '12'"
          class="text-left"
        >
          <v-icon v-if="waypoint.avatar">
            {{ getIcon(waypoint.type,waypoint.role) }} 
          </v-icon>
          <v-icon v-if="noticeableDetour && waypoint.role=='passenger'">
            mdi-clock
          </v-icon><span :class="'passenger' == waypoint.role ? 'font-weight-bold' : ''">{{ waypoint.address.addressLocality }}</span> {{ waypoint.address.venue ? ' - ' + waypoint.address.venue : waypoint.address.streetAddress ? ' - ' + waypoint.address.streetAddress : null }}
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
    noticeableDetour:{
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
      message:null
    };
  },
  //icon:
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
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
    getTooltipMessage (type,role){
      if (role == 'driver') {
        if (type == 'origin') return this.$t('driverOrigin'); 
        if (type == 'destination') return this.$t('driverDestination');
        if (type == 'step') return this.$t('step');
      } else {
        if (type == 'origin') return this.$t('pickUp');
        if (type == 'destination') return this.$t('dropOff');
        if (type == 'step') return this.$t('step');
      }
    }, 
    formatTime(time) {
      if(time) return moment.utc(time).format(this.$t("hourMinute")); 
    }
  }
};
</script>
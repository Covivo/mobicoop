<template>
  <v-container fluid>
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
      >
        <!-- First line : dates, price -->
        <v-row
          align="center"
          dense
        >
          <v-col
            :cols="displayInfo ? 6 : 10" 
            offset="1"
            align="left"
          >
            <!-- dates -->
            <h2
              v-if="!regular"
            >
              {{ computedOutwardDateFormat }}
            </h2>
            <h2 v-else>
              <v-chip
                :color="activeDays.mon ? 'success' : 'default'"
              >
                {{ $t('ui.abbr.day.mon') }}
              </v-chip>
              <v-chip
                :color="activeDays.tue ? 'success' : 'default'"
              >
                {{ $t('ui.abbr.day.tue') }}
              </v-chip>
              <v-chip
                :color="activeDays.wed ? 'success' : 'default'"
              >
                {{ $t('ui.abbr.day.wed') }}
              </v-chip>
              <v-chip
                :color="activeDays.thu ? 'success' : 'default'"
              >
                {{ $t('ui.abbr.day.thu') }}
              </v-chip>
              <v-chip
                :color="activeDays.fri ? 'success' : 'default'"
              >
                {{ $t('ui.abbr.day.fri') }}
              </v-chip>
              <v-chip
                :color="activeDays.sat ? 'success' : 'default'"
              >
                {{ $t('ui.abbr.day.sat') }}
              </v-chip>
              <v-chip
                :color="activeDays.sun ? 'success' : 'default'"
              >
                {{ $t('ui.abbr.day.sun') }}
              </v-chip>
            </h2>
          </v-col>
        
          <!-- price -->
          <v-col
            v-if="displayInfo"
            cols="3"
            offset="1"
            align="right"
          >
            <h2>{{ solidary ? 0 : price }} €</h2>
          </v-col>
        </v-row>

        <!-- Second line : seats -->
        <v-row
          v-if="driver && displayInfo"
          align="center"
          dense
        >
          <v-col
            cols="6"
            offset="1"
            align="left"
          >
            <p>{{ seats }} {{ $tc('places',seats) }}</p>
          </v-col>
        </v-row>

        <v-row
          align="center"
          dense
        >
          <v-col
            :cols="displayInfo ? 6 : 10" 
            offset="1"
            align="left"
          >
            <v-divider />
          </v-col>
        </v-row>
        
        <!-- Third line : direction, days, user, message -->
        <v-row
          align="center"
          dense
        >
          <!-- Direction, days, message -->
          <v-col
            :cols="displayInfo ? 6 : 10" 
            offset="1"
            align="left"
          >
            <!-- Direction -->
            <v-row
              align="center"
            >
              <v-timeline
                dense
              >
                <v-timeline-item 
                  color="success"
                  small
                >
                  <v-row dense>
                    <v-col 
                      v-if="!regular"
                      :cols="displayInfo ? 6 : 10" 
                    >
                      <strong>{{ computedOutwardTimeFormat }}</strong>
                    </v-col>
                    <v-col :cols="displayInfo ? 6 : 12">
                      {{ (route && route.origin) ? route.origin.addressLocality : null }}
                    </v-col>
                  </v-row>
                </v-timeline-item>

                <v-timeline-item 
                  v-for="waypoint in activeWaypoints"
                  :key="waypoint.id"
                  small
                >
                  <v-row dense>
                    <v-col :cols="displayInfo ? 6 : 12">
                      {{ waypoint.address.addressLocality }}
                    </v-col>
                  </v-row>
                </v-timeline-item>

                <v-timeline-item 
                  color="success"
                  small
                >
                  <v-row dense>
                    <v-col 
                      v-if="!regular"
                      :cols="displayInfo ? 6 : 10" 
                    >
                      <strong>{{ computedDestinationTime }}</strong>
                    </v-col>
                    <v-col :cols="displayInfo ? 6 : 12">
                      {{ (route && route.destination) ? route.destination.addressLocality : null }}
                    </v-col>
                  </v-row>
                </v-timeline-item>
              </v-timeline>
            </v-row>

            <!-- Days if regular and there is more than one schedule -->
            <v-row
              v-if="regular && schedules!==null && schedules.length > 1"
              align="center"
              class="mt-2"
            >
              <v-col
                cols="12"
              >
                <v-row
                  v-for="schedule in schedules"
                  :key="schedule.id"
                  dense
                >
                  <v-col
                    cols="12"
                  >
                    <v-chip
                      small
                      :color="schedule.mon ? 'success' : 'default'"
                    >
                      {{ $t('ui.abbr.day.mon') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.tue ? 'success' : 'default'"
                    >
                      {{ $t('ui.abbr.day.tue') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.wed ? 'success' : 'default'"
                    >
                      {{ $t('ui.abbr.day.wed') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.thu ? 'success' : 'default'"
                    >
                      {{ $t('ui.abbr.day.thu') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.fri ? 'success' : 'default'"
                    >
                      {{ $t('ui.abbr.day.fri') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.sat ? 'success' : 'default'"
                    >
                      {{ $t('ui.abbr.day.sat') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.sun ? 'success' : 'default'"
                    >
                      {{ $t('ui.abbr.day.sun') }}
                    </v-chip>
                  </v-col>

                  <!-- Outward -->
                  <v-col
                    cols="12"
                  >
                    <v-row
                      dense
                    >
                      <v-col
                        cols="2"
                      >
                        {{ $t('outward') }}
                      </v-col>
                      <v-col
                        cols="2"
                      >
                        <v-icon
                          slot="prepend"
                        >
                          mdi-arrow-right-circle
                        </v-icon>
                      </v-col>
                      <v-col
                        cols="2"
                      >
                        {{ formatTime(schedule.outwardTime) }}
                      </v-col>
                    </v-row>
                  </v-col>

                  <!-- Return -->
                  <v-col
                    v-if="schedule.returnTime"
                    cols="12"
                  >
                    <v-row
                      dense
                    >
                      <v-col
                        cols="2"
                      >
                        {{ $t('return') }}
                      </v-col>
                      <v-col
                        cols="2"
                      >
                        <v-icon
                          slot="prepend"
                        >
                          mdi-arrow-left-circle
                        </v-icon>
                      </v-col>
                      <v-col
                        cols="2"
                      >
                        {{ formatTime(schedule.returnTime) }}
                      </v-col>
                    </v-row>
                  </v-col>

                  <v-col
                    cols="12"
                  >
                    <v-divider />
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
           
            <!-- Message -->
            <v-row
              v-else
              align="center"
              class="mt-2"
            >
              <v-col
                cols="12"
              >
                <v-card
                  v-if="displayInfo"
                  outlined
                  class="mx-auto"
                > 
                  <v-card-text class="pre-formatted">
                    {{ message }}
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-col>

          <!-- User -->
          <v-col
            v-if="displayInfo"
            cols="3"
            offset="1"
            align="center"
          >
            <v-row dense>
              <v-col
                cols="12"
              >
                <v-card

                  outlined
                  class="mx-auto"
                >
                  <v-card-title v-if="user != null">
                    {{ user.givenName }} {{ user.familyName }}
                  </v-card-title>
                  <v-card-text v-if="user != null">
                    {{ user.telephone }}
                  </v-card-text>
                </v-card>
              </v-col>            
            </v-row>
          </v-col>
        </v-row>

        <!-- Fourth line : message if regular -->
        <v-row
          v-if="regular && displayInfo"
          align="center"
          justify="center"
          dense
        >
          <v-col
            cols="10"
            align="left"
          >
            <v-card
              outlined
              class="mx-auto"
            > 
              <v-card-text class="pre-formatted">
                {{ message }}
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/publish/AdSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/publish/AdSummary.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    driver: {
      type: Boolean,
      default: false
    },
    passenger: {
      type: Boolean,
      default: false
    },
    regular: {
      type: Boolean,
      default: false
    },
    outwardDate: {
      type: String,
      default: null
    },
    outwardTime: {
      type: String,
      default: null
    },
    returnDate: {
      type: String,
      default: null
    },
    returnTime: {
      type: String,
      default: null
    },
    schedules: {
      type: Array,
      default: null,
    },
    origin: {
      type: Object,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    waypoints: {
      type: Object,
      default: null
    },
    price: {
      type: Number,
      default: null
    },
    seats: {
      type: Number,
      default: null
    },
    message: {
      type: String,
      default: null
    },
    route: {
      type: Object,
      default: null
    },
    // on true display price, seats, user info and message 
    displayInfo: {
      type: Boolean,
      default: true
    },
    solidary: {
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
    computedOutwardDateFormat() {
      moment.locale(this.locale);
      return this.outwardDate
        ? moment(this.outwardDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    computedReturnDateFormat() {
      moment.locale(this.locale);
      return this.returnDate
        ? moment(this.returnDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    computedOutwardTimeFormat() {
      moment.locale(this.locale);
      return (this.outwardDate && this.outwardTime)
        ? moment(this.outwardDate+' '+this.outwardTime).format(this.$t("ui.i18n.time.format.hourMinute"))
        : null;
    },
    computedDestinationTime() {
      moment.locale(this.locale);
      if (this.route && this.route.direction && this.outwardDate && this.outwardTime) {
        return moment(this.outwardDate+' '+this.outwardTime).add(this.route.direction.duration,'seconds').format(this.$t("ui.i18n.time.format.hourMinute"));
      }
      return null;
    },
    activeWaypoints() {
      if (this.route && this.route.waypoints) {
        return this.route.waypoints.filter(function(waypoint) {
          return waypoint.visible && waypoint.address;
        });
      }
      return null;
    },
    activeDays() {
      let days = {
        mon: false,
        tue: false,
        wed: false,
        thu: false,
        fri: false,
        sat: false,
        sun: false,
      };
      if (this.regular && this.schedules) {
        for (var i=0;i<this.schedules.length;i++) {
          if (this.schedules[i].mon) days.mon = true;
          if (this.schedules[i].tue) days.tue = true;
          if (this.schedules[i].wed) days.wed = true;
          if (this.schedules[i].thu) days.thu = true;
          if (this.schedules[i].fri) days.fri = true;
          if (this.schedules[i].sat) days.sat = true;
          if (this.schedules[i].sun) days.sun = true;
        }
      }
      return days;
    }
  },
  methods: {
    formatTime(time) {
      moment.locale(this.locale);
      return moment(moment(new Date()).format('Y-MM-DD')+' '+time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
};
</script>

<style scoped>
.pre-formatted {
  white-space: pre;
}
</style>
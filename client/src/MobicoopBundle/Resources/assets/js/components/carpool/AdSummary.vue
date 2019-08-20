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
          justify="center"
          dense
        >
          <v-col
            cols="7"
            align="left"
          >
            <!-- dates -->
            <h2
              v-if="!regular"
            >
              {{ computedOutwardDateFormat }}
            </h2>
            <h2 v-else>
              Regulier
            </h2>
          </v-col>
        
          <!-- price -->
          <v-col
            cols="3"
            offset="1"
            align="right"
          >
            <h2>{{ price }} €</h2>
          </v-col>
        </v-row>

        <!-- Second line : seats -->
        <v-row
          v-if="driver"
          align="center"
          justify="center"
          dense
        >
          <v-col
            cols="11"
            align="left"
          >
            <p>{{ seats }} {{ $tc('places',seats) }}</p>
          </v-col>
        </v-row>

        <!-- Third line : direction, user, message -->
        <v-row
          align="center"
          justify="center"
          dense
        >
          <!-- Direction, message -->
          <v-col
            cols="7"
            align="left"
          >
            <!-- Direction -->
            <v-row
              v-if="!regular"
              align="center"
            >
              <ul>
                <li>{{ computedOutwardTimeFormat }} : {{ (route && route.origin) ? route.origin.addressLocality : null }} </li>
                <li
                  v-for="waypoint in activeWaypoints"
                  :key="waypoint.id"
                >
                  {{ waypoint.address.addressLocality }}
                </li>
                <li>{{ computedDestinationTime }} : {{ (route && route.destination) ? route.destination.addressLocality : null }} </li>
              </ul>
            </v-row>

            <!-- Message -->
            <v-row
              v-if="!regular"
              align="center"
              class="mt-2"
            >
              <v-card>
                <v-row
                  align="center"
                  justify="space-around"
                >
                  <v-col
                    cols="12"
                  >
                    {{ message }}
                  </v-col>
                </v-row>
              </v-card>
            </v-row>
          </v-col>

          <!-- User -->
          <v-col
            cols="3"
            offset="1"
            align="center"
          >
            <h3>{{ user.givenName }} {{ user.familyName }}</h3>
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
import Translations from "@translations/components/carpool/AdSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/AdSummary.json";

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
      type: Object,
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
    }
  },
  methods: {
  }
};
</script>

<style scoped>
.pre-formatted {
  white-space: pre;
}
</style>
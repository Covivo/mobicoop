<template>
  <v-container fluid>
    <v-row>
      <!--      Display travel informations-->
      <v-col
        cols="12"
        md="8"
        xl="6"
      >
        <!-- First line : dates -->
        <v-row
          align="center"
          dense
        >
          <v-col
            :cols="displayInfo ? 10 : 10"
            offset="1"
            align="left"
          >
            <!-- dates -->
            <h2
              v-if="!regular"
            >
              <span
                v-if="hasReturn"
                class="secondary--text"
              >
                {{ $t('outward') }}
                <v-icon color="secondary">
                  mdi-arrow-right
                </v-icon>
              </span>

              <span>{{ computedOutwardDateFormat }}</span>
            </h2>
            <h2 v-else>
              <v-chip
                :color="activeDays.mon ? 'success' : 'default'"
              >
                {{ $t('mon') }}
              </v-chip>
              <v-chip
                :color="activeDays.tue ? 'success' : 'default'"
              >
                {{ $t('tue') }}
              </v-chip>
              <v-chip
                :color="activeDays.wed ? 'success' : 'default'"
              >
                {{ $t('wed') }}
              </v-chip>
              <v-chip
                :color="activeDays.thu ? 'success' : 'default'"
              >
                {{ $t('thu') }}
              </v-chip>
              <v-chip
                :color="activeDays.fri ? 'success' : 'default'"
              >
                {{ $t('fri') }}
              </v-chip>
              <v-chip
                :color="activeDays.sat ? 'success' : 'default'"
              >
                {{ $t('sat') }}
              </v-chip>
              <v-chip
                :color="activeDays.sun ? 'success' : 'default'"
              >
                {{ $t('sun') }}
              </v-chip>
            </h2>
          </v-col>
        </v-row>

        <!-- Second line : seats -->
        <v-row
          v-if="driver && displayInfo"
          align="center"
          dense
        >
          <v-col
            cols="10"
            offset="1"
            align="left"
          >
            <p>{{ seats }} {{ $tc('places',seats) }}</p>
          </v-col>
        </v-row>
        <!--divider-->
        <v-row
          align="center"
          dense
        >
          <v-col
            :cols="displayInfo ? 8 : 10"
            offset="1"
            align="left"
          >
            <v-divider class="mb-3" />
          </v-col>
        </v-row>

        <!-- Third line : direction, days, message -->
        <v-row
          dense
        >
          <!-- Direction, days, message -->
          <v-col
            :cols="displayInfo ? 8 : 10"
            offset="1"
            align="left"
          >
            <!-- Direction -->
            <v-row
              align="center"
            >
              <v-timeline
                dense
                class="py-0"
                width="100"
              >
                <v-timeline-item
                  color="primary"
                  medium
                  class="mb-3"
                  width="100%"
                >
                  <v-row dense>
                    <v-col
                      v-if="!regular"
                      :cols="displayInfo ? 6 : 10"
                    >
                      <strong>{{ computedOutwardTimeFormat }}</strong>
                    </v-col>
                    <v-col :cols="displayInfo ? 12 : 12">
                      <span>
                        {{ (route && route.origin) ? displayAddress(route.origin) : '' }}
                      </span>
                    </v-col>
                  </v-row>
                </v-timeline-item>

                <!--                <v-timeline-item-->
                <!--                  v-for="waypoint in activeWaypoints"-->
                <!--                  :key="waypoint.id"-->
                <!--                  small-->
                <!--                >-->
                <v-row
                  v-for="waypoint in activeWaypoints"
                  :key="waypoint.id"
                  dense
                  class="ml-12"
                >
                  <v-col :cols="displayInfo ? 12 : 12">
                    <v-row class="ml-12">
                      <v-icon
                        color="secondary"
                        class="mr-3"
                      >
                        mdi-arrow-right-bold
                      </v-icon> {{ displayAddress(waypoint.address) }}
                    </v-row>
                  </v-col>
                </v-row>
                <!--                </v-timeline-item>-->

                <v-timeline-item
                  color="primary"
                  medium
                  class="mt-3 pb-0"
                >
                  <v-row dense>
                    <v-col
                      v-if="!regular"
                      :cols="displayInfo ? 6 : 10"
                    >
                      <strong>{{ computedDestinationTime }}</strong>
                    </v-col>
                    <v-col :cols="displayInfo ? 12 : 12">
                      {{ (route && route.destination) ? displayAddress(route.destination) : '' }}
                    </v-col>
                  </v-row>
                </v-timeline-item>
              </v-timeline>
            </v-row>

            <!-- Return if not regular and has return -->
            <v-divider class="my-3" />
            <v-row
              v-if="hasReturn && !regular"
              align="center"
            >
              <v-row>
                <v-row
                  dense
                  align="center"
                >
                  <v-col
                    cols="12"
                    class="ml-6"
                  >
                    <h2
                      v-if="!regular"
                    >
                      <v-row
                        v-if="hasReturn"
                        class="secondary--text"
                      >
                        {{ $t('return') }}
                        <v-icon color="secondary">
                          mdi-arrow-left
                        </v-icon>
                      </v-row>

                      {{ computedReturnDateFormat }}
                    </h2>
                    <p
                      v-if="driver && displayInfo"
                    >
                      {{ seats }} {{ $tc('places',seats) }}
                    </p>
                  </v-col>
                </v-row>
              </v-row>
              <v-col
                cols="12"
              >
                <v-row
                  align="center"
                  dense
                >
                  <!--                    <v-col-->
                  <!--                      :cols="12"-->
                  <!--                    >-->
                  <v-divider class="mb-2" />
                  <!--                    </v-col>-->
                </v-row>
              </v-col>
              <v-row dense>
                <v-col
                  :cols="displayInfo ? 8 : 10"
                  align="left"
                  class="ml-3"
                >
                  <v-row
                    align="center"
                  >
                    <v-timeline
                      dense
                      class="py-0"
                    >
                      <v-timeline-item
                        color="primary"
                        medium
                      >
                        <v-row dense>
                          <v-col
                            v-if="!regular"
                            :cols="displayInfo ? 6 : 10"
                          >
                            <strong>{{ computedReturnOutwardTimeFormat }}</strong>
                          </v-col>
                          <v-col :cols="displayInfo ? 6 : 12">
                            <!-- return so we invert destination and origin-->
                            <span>{{ (route && route.destination) ? route.destination.addressLocality : null }}</span>
                          </v-col>
                        </v-row>
                      </v-timeline-item>

                      <!--                <v-timeline-item-->
                      <!--                  v-for="waypoint in reversedActiveWaypoints"-->
                      <!--                  :key="waypoint.id"-->
                      <!--                  small-->
                      <!--                >-->
                      <v-row
                        v-for="waypoint in reversedActiveWaypoints"
                        :key="waypoint.id"
                        dense
                        class="ml-12"
                      >
                        <v-col :cols="displayInfo ? 12 : 12">
                          <v-row class="ml-12">
                            <v-icon
                              color="secondary"
                              class="mr-3"
                            >
                              mdi-arrow-right-bold
                            </v-icon>
                            {{ waypoint.address.addressLocality }}
                          </v-row>
                        </v-col>
                      </v-row>
                      <!--                </v-timeline-item>-->

                      <v-timeline-item
                        color="primary"
                        medium
                        class="mt-3 pb-0"
                      >
                        <v-row dense>
                          <v-col
                            v-if="!regular"
                            :cols="displayInfo ? 6 : 10"
                          >
                            <strong>{{ computedReturnDestinationTime }}</strong>
                          </v-col>
                          <v-col :cols="displayInfo ? 6 : 12">
                            <!-- return so we invert destination and origin-->
                            <span>{{ (route && route.origin) ? route.origin.addressLocality : null }}</span>
                          </v-col>
                        </v-row>
                      </v-timeline-item>
                    </v-timeline>
                  </v-row>
                </v-col>
              </v-row>
            </v-row>
     
            <!-- Days if regular and there is more than one schedule -->
            <v-row
              v-if="regular && schedules!==null && schedules.length > 0"
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
                      {{ $t('mon') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.tue ? 'success' : 'default'"
                    >
                      {{ $t('tue') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.wed ? 'success' : 'default'"
                    >
                      {{ $t('wed') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.thu ? 'success' : 'default'"
                    >
                      {{ $t('thu') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.fri ? 'success' : 'default'"
                    >
                      {{ $t('fri') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.sat ? 'success' : 'default'"
                    >
                      {{ $t('sat') }}
                    </v-chip>
                    <v-chip
                      small
                      :color="schedule.sun ? 'success' : 'default'"
                    >
                      {{ $t('sun') }}
                    </v-chip>
                  </v-col>

                  <!-- Outward -->
                  <v-col
                    v-if="schedule.outwardTime"
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
                  v-if="displayInfo && !regular"
                  outlined
                  class="mx-auto"
                >
                  <v-card-text
                    style="white-space: pre-line"
                  >
                    {{ message }}
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
              <v-card-text
                style="white-space: pre-line"
              >
                {{ message }}
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>

      <!--      Display price and informations of the user-->
      <v-col
        cols="12"
        class="col-lg-4 pt-0"
      >
        <!-- price -->
        <v-col
          v-if="displayInfo && !solidaryExclusive"
          cols="12"
        >
          <v-row
            class="mb-8"
          >
            <v-col
              cols="12"
              class="pt-0"
            >
              <h2
                v-if="!passenger"
                class="text-right mr-6"
                align="right"
              >
                {{ price }} €
              </h2>
            </v-col>
          </v-row>
          <v-row>
            <!-- User -->
            <v-col
              v-if="displayInfo"
              cols="12"
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
                    <img
                      v-if="user.avatars[0]"
                      :src="user['avatars'][0]"
                      alt="avatar"
                    >
                    <img
                      v-else
                      :src="urlAltAvatar"
                      alt="avatar"
                    >

                    <v-card-text
                      v-if="user != null"
                      class="text-h6"
                    >
                      {{ user.givenName }} {{ user.shortFamilyName }}
                      <v-card-text v-if="ageDisplay">
                        {{ birthDate }} ans
                      </v-card-text>
                    </v-card-text>
                    <v-divider />
                    <v-card-text v-if="user != null">
                      {{ user.telephone }}
                    </v-card-text>
                    <v-card-text v-if="user != null">
                      {{ user.email }}
                    </v-card-text>
                    <v-divider />
                    <v-card-text v-if="user != null">
                      {{ user.smoke == 0 ? 'Non fumeur' : 'Fumeur' }}
                    </v-card-text>
                    <v-card-text v-if="user != null">
                      {{ user.music == 0 ? "Je préfère rouler sans fond sonore": "J’écoute la radio ou de la musique" }}
                    </v-card-text>
                    <v-card-text v-if="user != null">
                      {{ user.chat == 0 ? "Je ne suis pas bavard" : "Je discute" }}
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-col>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/publish/AdSummary/";

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
    solidaryExclusive: {
      type: Boolean,
      default: false
    },
    ageDisplay: {
      type: Boolean,
      default: false
    },
    eventId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      birthDate: null
    };
  },
  computed: {
    computedOutwardDateFormat() {
      return this.outwardDate
        ? moment(this.outwardDate).format(this.$t("fullDate"))
        : "";
    },
    computedReturnDateFormat() {
      return this.returnDate
        ? moment(this.returnDate).format(this.$t("fullDate"))
        : "";
    },
    computedOutwardTimeFormat() {
      return (this.outwardDate && this.outwardTime)
        ? moment(this.outwardDate+' '+this.outwardTime).isValid()
          ? moment(this.outwardDate+' '+this.outwardTime).format(this.$t("hourMinute"))
          : moment(this.outwardTime).format(this.$t("hourMinute"))
        : null;
    },
    computedDestinationTime() {
      if (this.route && this.route.direction && this.outwardDate && this.outwardTime) {
        return moment(this.outwardDate+' '+this.outwardTime).isValid()
          ? moment(this.outwardDate+' '+this.outwardTime).add(this.route.direction.duration,'seconds').format(this.$t("hourMinute"))
          : moment(this.outwardTime).add(this.route.direction.duration,'seconds').format(this.$t("hourMinute")) ;
      }
      return null;
    },
    computedReturnOutwardTimeFormat() {
      return (this.hasReturn)
        ? moment(this.returnDate+' '+this.returnTime).isValid()
          ? moment(this.returnDate+' '+this.returnTime).format(this.$t("hourMinute"))
          : moment(this.returnTime).format(this.$t("hourMinute"))
        : null;
    },
    computedReturnDestinationTime() {
      if (this.route && this.route.direction && this.hasReturn) {
        return moment(this.returnDate+' '+this.returnTime).isValid()
          ? moment(this.returnDate+' '+this.returnTime).add(this.route.direction.duration,'seconds').format(this.$t("hourMinute"))
          : moment(this.returnTime).add(this.route.direction.duration,'seconds').format(this.$t("hourMinute"));
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
    reversedActiveWaypoints() {
      if (this.route && this.route.waypoints) {
        return this.reversedArray(this.route.waypoints).filter(function(waypoint) {
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
    },
    hasReturn () {
      return this.returnDate !== null && this.returnTime !== null;
    }
  },
  mounted() {
    this.birthDate = moment(moment(new Date()).format('Y-MM-DD')).diff(moment(this.user.birthDate.date).format('Y-MM-DD'), 'years')
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    formatTime(time) {
      return moment(moment(new Date()).format('Y-MM-DD')+' '+time).format(this.$t("hourMinute"));
    },
    reversedArray(array) {
      // slice to make a copy of array, then reverse the copy
      return array.slice().reverse()
    },
    displayAddress(address){
      if(address.relayPoint && address.relayPoint.name){
        return address.relayPoint.name;
      }
      if(address.displayedLabel){
        return address.displayedLabel;
      }
      else{
        let display = address.addressLocality;
        if(address.streetAddress) display += '\n' + address.streetAddress;
        if(address.venue) display += '\n' + address.venue;
        return display;
      }
    }
  }
};
</script>
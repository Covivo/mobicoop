<template>
  <v-card>
    <v-toolbar
      color="primary"
    >
      <v-toolbar-title>
        {{ $t('detailTitle') }}
      </v-toolbar-title>
      
      <v-spacer />

      <v-btn 
        icon
        @click="$emit('close')"
      >
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-toolbar>

    <v-stepper
      v-model="step"
      alt-labels
      class="elevation-0"
    >
      <v-stepper-items>
        <!-- Step 1 : journey detail -->
        <v-stepper-content step="1">
          <v-card-text>
            <!-- Date / seats / price -->
            <v-row
              align="center"
              dense
            >
              <!-- Date -->
              <v-col
                v-if="!regular"
                cols="5"
                class="title text-center"
              >
                {{ computedDate }}
              </v-col>

              <v-col
                v-else
                cols="5"
                class="title text-center"
              >
                <regular-days-summary 
                  :mon-active="lResult.monCheck"
                  :tue-active="lResult.tueCheck"
                  :wed-active="lResult.wedCheck"
                  :thu-active="lResult.thuCheck"
                  :fri-active="lResult.friCheck"
                  :sat-active="lResult.satCheck"
                  :sun-active="lResult.sunCheck"
                />
              </v-col>

              <!-- Seats -->
              <v-col
                cols="3"
                class="title text-center"
              >
                {{ $tc('places', lResult.seats, { seats: lResult.seats }) }}
              </v-col>

              <!-- Price -->
              <v-col
                cols="4"
                class="title text-center"
              >
                {{ lResult.roundedPrice ? lResult.roundedPrice +'€' : '' }}
              </v-col>
            </v-row>

            <!-- Route / carpooler -->
            <v-row
              align="center"
              dense
            > 
              <!-- Route -->
              <v-col
                cols="8"
              >
                <v-row>
                  <v-col>
                    <v-journey
                      :time="lResult.time || lResult.outwardTime ? true : false"
                      :waypoints="waypoints"
                    />
                  </v-col>
                </v-row>
                <v-row 
                  v-if="lResult.comment"
                >
                  <v-col>
                    <v-card
                      outlined
                      class="mx-auto"
                    > 
                      <v-card-text class="pre-formatted">
                        {{ lResult.comment }}
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </v-col>

              <!-- Carpooler -->
              <v-col
                cols="4"
              >
                <v-card>
                  <!-- Avatar -->
                  <v-img
                    aspect-ratio="2"
                    :src="result.carpooler.avatars[result.carpooler.avatars.length-1]"
                  />
                  <v-card-title>
                    <v-row
                      dense
                    >
                      <v-col
                        class="text-center"
                      >
                        {{ lResult.carpooler.givenName }} {{ lResult.carpooler.shortFamilyName }}
                      </v-col>
                    </v-row>
                  </v-card-title>
                  <v-card-text>
                    <v-row
                      dense
                    >
                      <v-col
                        cols="12"
                        class="text-center"
                      >
                        {{ age }}
                      </v-col>
                      <v-col
                        cols="12"
                        class="text-center"
                      >
                        {{ lResult.carpooler.telephone }}
                      </v-col>
                      
                      <v-col  
                        cols="12"
                        class="text-center"
                      >
                        <v-btn
                          color="primary"
                          :disabled="contactDisabled"
                          :loading="contactLoading"
                          @click="contact"
                        >
                          <v-icon>
                            mdi-email
                          </v-icon>
                          {{ $t('contact') }}
                        </v-btn>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-stepper-content>

        <!-- Step 2 : outward and date range -->
        <v-stepper-content step="2">
          <v-row
            align="center"
          >
            <!-- Start date -->
            <v-col
              cols="3"
            >
              <v-menu
                v-model="menuFromDate"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    :value="computedFromDate"
                    :label="$t('fromDate')"
                    readonly
                    v-on="on"
                  />
                </template>
                <v-date-picker
                  v-model="fromDate"
                  :locale="locale"
                  :min="today"
                  :max="toDate ? toDate : null"
                  no-title
                  @input="menuFromDate = false"
                  @change="change"
                />
              </v-menu>
            </v-col>

            <!-- Slider -->
            <v-col
              cols="6"
            >
              <v-slider
                v-model="range"
                :tick-labels="$t('ranges')"
                max="3"
                step="1"
                ticks="always"
                tick-size="8"
                @change="change"
              />
            </v-col>

            <!-- End date -->
            <v-col
              cols="3"
            >
              <v-menu
                v-model="menuMaxDate"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    :value="computedMaxDate"
                    :label="$t('maxDate')"
                    readonly
                    :disabled="range<3"
                    v-on="on"
                  />
                </template>
                <v-date-picker
                  v-model="maxDate"
                  :locale="locale"
                  :min="fromDate"
                  :max="toDate ? toDate : null"
                  no-title
                  @input="menuMaxDate = false"
                  @change="change"
                />
              </v-menu>
            </v-col>
          </v-row>

          <regular-ask 
            :type="1"
            :mon-time="outwardMonTime"
            :tue-time="outwardTueTime"
            :wed-time="outwardWedTime"
            :thu-time="outwardThuTime"
            :fri-time="outwardFriTime"
            :sat-time="outwardSatTime"
            :sun-time="outwardSunTime"
            :origin-driver="lResult.resultDriver ? lResult.originDriver : null"
            :destination-driver="lResult.resultDriver ? lResult.destinationDriver : null"
            :origin-passenger="lResult.originPassenger"
            :destination-passenger="lResult.destinationPassenger"
            :from-date="fromDate"
            :max-date="maxDate"
            @change="changeOutward"
          />
        </v-stepper-content>

        <!-- Step 3 : return -->
        <v-stepper-content step="3">
          <regular-ask
            :type="2"
            :mon-time="returnMonTime"
            :tue-time="returnTueTime"
            :wed-time="returnWedTime"
            :thu-time="returnThuTime"
            :fri-time="returnFriTime"
            :sat-time="returnSatTime"
            :sun-time="returnSunTime"
            :origin-driver="lResult.resultDriver ? lResult.destinationDriver : null"
            :destination-driver="lResult.resultDriver ? lResult.originDriver : null"
            :origin-passenger="lResult.destinationPassenger"
            :destination-passenger="lResult.originPassenger"
            :from-date="fromDate"
            :max-date="maxDate"
            @change="changeReturn"
          />
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>

    <!-- Action buttons -->
    <v-card-actions
      v-if="(driver ^ passenger) || (driver && passenger && lResult.frequency == 1)"
    >
      <v-spacer />
      <!-- Carpool (driver xor passenger) -->
      <v-btn
        v-if="(driver ^ passenger) && step == 1"
        color="secondary"
        :disabled="carpoolDisabled"
        :loading="carpoolLoading"
        @click="lResult.frequency == 1 ? (driver ? carpool(1) : carpool(2)) : step = 2"
      >
        {{ lResult.frequency == 1 ? $t('carpool') : $t('outward') }}
      </v-btn>

      <!-- Carpool (driver) --> 
      <v-btn
        v-if="driver && passenger"
        color="secondary"
        :disabled="carpoolDisabled"
        :loading="carpoolLoading"
        @click="carpool(1)"
      >
        {{ $t('carpoolAsDriver') }}
      </v-btn>

      <!-- Carpool (passenger) --> 
      <v-btn
        v-if="driver && passenger"
        color="secondary"
        :disabled="carpoolDisabled"
        :loading="carpoolLoading"
        @click="carpool(2)"
      >
        {{ $t('carpoolAsPassenger') }}
      </v-btn>

      <!-- Step 2 or 3 (previous) --> 
      <v-btn
        v-if="step > 1"
        color="secondary"
        outlined
        @click="step--"
      >
        {{ $t('previous') }}
      </v-btn>

      <!-- Step 2 (regular outward, return available) --> 
      <v-btn
        v-if="step == 2 && lResult.return"
        color="secondary"
        @click="step = 3"
      >
        {{ $t('return') }}
      </v-btn>

      <!-- Step 2 (regular outward, no return) --> 
      <v-btn
        v-if="step == 2 && !lResult.return && outwardTrip.length>0"
        color="secondary"
        @click="driver ? carpool(1) : carpool(2)"
      >
        {{ $t('carpool') }}
      </v-btn>

      <!-- Step 3 (regular return) --> 
      <v-btn
        v-if="step == 3 && (outwardTrip.length > 0 || returnTrip.length>0)"
        color="secondary"
        @click="driver ? carpool(1) : carpool(2)"
      >
        {{ $t('carpool') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/carpool/results/MatchingJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/MatchingJourney.json";
import VJourney from "@components/carpool/utilities/VJourney";
import RegularDaysSummary from "@components/carpool/utilities/RegularDaysSummary";
import RegularAsk from "@components/carpool/utilities/RegularAsk";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    VJourney,
    RegularDaysSummary,
    RegularAsk
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    result: {
      type: Object,
      default: null
    },
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      lResult: this.result,
      contactLoading: false,
      carpoolLoading: false,
      contactDisabled: false,
      carpoolDisabled: false,
      step:1,
      fromDate: this.result.startDate ? this.result.startDate : null,
      menuFromDate: false,
      maxDate: this.result.startDate ? this.result.startDate : null,
      menuMaxDate: false,
      toDate: this.result.toDate ? this.result.toDate : null,
      range: 0,
      outwardMonTime: null,
      outwardTueTime: null,
      outwardWedTime: null,
      outwardThuTime: null,
      outwardFriTime: null,
      outwardSatTime: null,
      outwardSunTime: null,
      returnMonTime: null,
      returnTueTime: null,
      returnWedTime: null,
      returnThuTime: null,
      returnFriTime: null,
      returnSatTime: null,
      returnSunTime: null,
      outwardTrip: [],
      returnTrip: []
    }
  },
  computed: {
    today() {
      return moment().toISOString();
    },
    driver() {
      return this.lResult && this.lResult.resultDriver ? true : false;
    },
    passenger() {
      return this.lResult && this.lResult.resultPassenger ? true : false;
    },
    regular() {
      return this.lResult && this.lResult.frequency == 2;
    },
    computedTime() {
      if (this.lResult && this.lResult.time) return moment.utc(this.lResult.time).format(this.$t("i18n.time.format.hourMinute"));      
      return null;
    },
    computedDate() {
      if (this.lResult && this.lResult.date) return moment.utc(this.lResult.date).format(this.$t("i18n.date.format.fullDate"));
      return null;
    },
    computedFromDate() {
      moment.locale(this.locale);
      return this.fromDate
        ? moment(this.fromDate).format(this.$t("i18n.date.format.shortDate"))
        : "";
    },
    computedMaxDate() {
      moment.locale(this.locale);
      return this.maxDate
        ? moment(this.maxDate).format(this.$t("i18n.date.format.shortDate"))
        : "";
    },
    age() {
      return this.lResult ? moment().diff(moment([this.lResult.carpooler.birthDate]),'years')+' '+this.$t("birthYears") : ''
    },
    waypoints() {
      return this.lResult.resultPassenger ? this.lResult.resultPassenger.outward.waypoints : this.lResult.resultDriver.outward.waypoints;
    }
  },
  watch: {
    result(val) {
      this.lResult = val;
      this.fromDate = val.startDate ? val.startDate : null;
      this.toDate = val.toDate ? val.toDate : null;
      this.computeTimes();
    }
  },
  mounted() {
    this.computeTimes();
  },
  methods: {
    computeMaxDate() {
      if (this.range == 1) {
        this.maxDate = moment(this.fromDate).add(1, 'M').toISOString();
      } else if (this.range == 2) {
        this.maxDate = moment(this.fromDate).add(3, 'M').toISOString();
      } else if (moment(this.maxDate).isBefore(moment(this.fromDate))) {
        this.maxDate = this.fromDate;
      }
    },
    computeTimes() {
      if (this.lResult.frequency == 2 && this.lResult.resultPassenger && !this.lResult.resultDriver) {  
        if (this.lResult.resultPassenger.outward) {
          this.outwardMonTime = this.lResult.resultPassenger.outward.monTime ? moment.utc(this.lResult.resultPassenger.outward.monTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardTueTime = this.lResult.resultPassenger.outward.tueTime ? moment.utc(this.lResult.resultPassenger.outward.tueTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardWedTime = this.lResult.resultPassenger.outward.wedTime ? moment.utc(this.lResult.resultPassenger.outward.wedTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardThuTime = this.lResult.resultPassenger.outward.thuTime ? moment.utc(this.lResult.resultPassenger.outward.thuTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardFriTime = this.lResult.resultPassenger.outward.friTime ? moment.utc(this.lResult.resultPassenger.outward.friTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardSatTime = this.lResult.resultPassenger.outward.satTime ? moment.utc(this.lResult.resultPassenger.outward.satTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardSunTime = this.lResult.resultPassenger.outward.sunTime ? moment.utc(this.lResult.resultPassenger.outward.sunTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        }
        if (this.lResult.resultPassenger.return) {
          this.returnMonTime = this.lResult.resultPassenger.return.monTime ? moment.utc(this.lResult.resultPassenger.return.monTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnTueTime = this.lResult.resultPassenger.return.tueTime ? moment.utc(this.lResult.resultPassenger.return.tueTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnWedTime = this.lResult.resultPassenger.return.wedTime ? moment.utc(this.lResult.resultPassenger.return.wedTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnThuTime = this.lResult.resultPassenger.return.thuTime ? moment.utc(this.lResult.resultPassenger.return.thuTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnFriTime = this.lResult.resultPassenger.return.friTime ? moment.utc(this.lResult.resultPassenger.return.friTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnSatTime = this.lResult.resultPassenger.return.satTime ? moment.utc(this.lResult.resultPassenger.return.satTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnSunTime = this.lResult.resultPassenger.return.sunTime ? moment.utc(this.lResult.resultPassenger.return.sunTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        }
      } else if (this.lResult.frequency == 2 && this.lResult.resultDriver && !this.lResult.resultPassenger) {  
        if (this.lResult.resultDriver.outward) {
          this.outwardMonTime = this.lResult.resultDriver.outward.monTime ? moment.utc(this.lResult.resultDriver.outward.monTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardTueTime = this.lResult.resultDriver.outward.tueTime ? moment.utc(this.lResult.resultDriver.outward.tueTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardWedTime = this.lResult.resultDriver.outward.wedTime ? moment.utc(this.lResult.resultDriver.outward.wedTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardThuTime = this.lResult.resultDriver.outward.thuTime ? moment.utc(this.lResult.resultDriver.outward.thuTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardFriTime = this.lResult.resultDriver.outward.friTime ? moment.utc(this.lResult.resultDriver.outward.friTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardSatTime = this.lResult.resultDriver.outward.satTime ? moment.utc(this.lResult.resultDriver.outward.satTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.outwardSunTime = this.lResult.resultDriver.outward.sunTime ? moment.utc(this.lResult.resultDriver.outward.sunTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        }
        if (this.lResult.resultDriver.return) {
          this.returnMonTime = this.lResult.resultDriver.return.monTime ? moment.utc(this.lResult.resultDriver.return.monTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnTueTime = this.lResult.resultDriver.return.tueTime ? moment.utc(this.lResult.resultDriver.return.tueTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnWedTime = this.lResult.resultDriver.return.wedTime ? moment.utc(this.lResult.resultDriver.return.wedTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnThuTime = this.lResult.resultDriver.return.thuTime ? moment.utc(this.lResult.resultDriver.return.thuTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnFriTime = this.lResult.resultDriver.return.friTime ? moment.utc(this.lResult.resultDriver.return.friTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnSatTime = this.lResult.resultDriver.return.satTime ? moment.utc(this.lResult.resultDriver.return.satTime).format(this.$t('i18n.time.format.hourMinute')) : null;
          this.returnSunTime = this.lResult.resultDriver.return.sunTime ? moment.utc(this.lResult.resultDriver.return.sunTime).format(this.$t('i18n.time.format.hourMinute')) : null;  
        }
      }
    },
    contact() {
      this.contactLoading = true;
      this.carpoolDisabled = true;
      let params = {
        "driver": this.lResult.resultDriver ? true : false,
        "passenger": this.lResult.resultPassenger ? true : false,
        "regular" : this.lResult.frequency == 2
      };
      let resultChoice = null;
      if (this.lResult.resultDriver) {
        resultChoice = this.lResult.resultDriver;
      } else {
        resultChoice = this.lResult.resultPassenger;
      }      
      params.proposalId = resultChoice.outward.proposalId;
      params.origin = resultChoice.outward.origin;
      params.destination = resultChoice.outward.destination;
      params.date = resultChoice.outward.date;
      params.time = resultChoice.outward.time;
      params.priceKm = resultChoice.outward.priceKm;
      params.outwardPrice = resultChoice.outward.originalPrice;
      params.outwardRoundedPrice = resultChoice.outward.originalRoundedPrice;
      params.outwardComputedPrice = resultChoice.outward.computedPrice;
      params.outwardComputedRoundedPrice = resultChoice.outward.computedRoundedPrice;
      if (resultChoice.return) {
        params.returnPrice = resultChoice.return.originalPrice;
        params.returnRoundedPrice = resultChoice.return.originalRoundedPrice;
        params.returnComputedPrice = resultChoice.return.computedPrice;
        params.returnComputedRoundedPrice = resultChoice.return.computedRoundedPrice;
      }
      if (resultChoice.outward.matchingId) {
        params.matchingId = resultChoice.outward.matchingId;
      }
      this.$emit('contact', params);
    },
    carpool(role) {
      this.carpoolLoading = true;
      this.contactDisabled = true;
      let params = {
        "driver": role==1,
        "passenger": role==2,
        "regular": this.lResult.frequency == 2,
        "outwardSchedule": this.getDays(this.outwardTrip),
        "returnSchedule": this.getDays(this.returnTrip),
        "fromDate": moment(this.fromDate).format(this.$t('i18n.date.format.computeDate')),
        "toDate": moment(this.maxDate).format(this.$t('i18n.date.format.computeDate'))
      };
      let resultChoice = this.lResult.resultDriver;
      if (role == 2) resultChoice = this.lResult.resultPassenger;
      params.proposalId = resultChoice.outward.proposalId;
      params.origin = resultChoice.outward.origin;
      params.destination = resultChoice.outward.destination;
      params.date = resultChoice.outward.date;
      params.time = resultChoice.outward.time;
      params.priceKm = resultChoice.outward.priceKm;
      params.outwardPrice = resultChoice.outward.originalPrice;
      params.outwardRoundedPrice = resultChoice.outward.originalRoundedPrice;
      params.outwardComputedPrice = resultChoice.outward.computedPrice;
      params.outwardComputedRoundedPrice = resultChoice.outward.computedRoundedPrice;
      if (resultChoice.return) {
        params.returnPrice = resultChoice.return.originalPrice;
        params.returnRoundedPrice = resultChoice.return.originalRoundedPrice;
        params.returnComputedPrice = resultChoice.return.computedPrice;
        params.returnComputedRoundedPrice = resultChoice.return.computedRoundedPrice;
      }
      if (resultChoice.outward.matchingId) {
        params.matchingId = resultChoice.outward.matchingId;
      }
      this.$emit('carpool', params);
    },
    change() {
      this.computeMaxDate();
    },
    changeOutward(params) {
      this.outwardTrip = params;
    },
    changeReturn(params) {
      this.returnTrip = params;
    },
    getDays(trip) {
      let days = {
        "monTime": null,
        "tueTime": null,
        "wedTime": null,
        "thuTime": null,
        "friTime": null,
        "satTime": null,
        "sunTime": null
      };
      for (var i = 0; i < trip.length; i++) {
        if (trip[i].day == "mon") days.monTime = trip[i].time.replace("h",":");
        if (trip[i].day == "tue") days.tueTime = trip[i].time.replace("h",":");
        if (trip[i].day == "wed") days.wedTime = trip[i].time.replace("h",":");
        if (trip[i].day == "thu") days.thuTime = trip[i].time.replace("h",":");
        if (trip[i].day == "fri") days.friTime = trip[i].time.replace("h",":");
        if (trip[i].day == "sat") days.satTime = trip[i].time.replace("h",":");
        if (trip[i].day == "sun") days.sunTime = trip[i].time.replace("h",":");
      }
      return days;
    }
  }
};
</script>
<style>
</style>
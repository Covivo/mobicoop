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
                {{ lResult.price ? lResult.price +'€' : '' }}
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
                    src="https://avataaars.io/?avatarStyle=Transparent&topType=ShortHairShortRound&accessoriesType=Blank&hairColor=BrownDark&facialHairType=Blank&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Default&skinColor=Light"
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
                :max="3"
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
            :origin="lResult.origin"
            :destination="lResult.destination"
            :from-date="fromDate"
            :max-date="maxDate"
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
            :origin="lResult.destination"
            :destination="lResult.origin"
            :from-date="fromDate"
            :max-date="maxDate"
          />
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>

    <!-- Action buttons -->
    <v-card-actions
      v-if="(driver ^ passenger) || (driver && passenger && lResult.frequency == 1)"
    >
      <!-- Carpool (driver xor passenger) -->
      <v-btn
        v-if="(driver ^ passenger) && step == 1"
        color="secondary"
        :disabled="carpoolDisabled"
        :loading="carpoolLoading"
        @click="lResult.frequency == 1 ? carpool(0) : step = 2"
      >
        {{ $t('outward') }}
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
        {{ step == 2 ? $t('detail') : $t('outward') }}
      </v-btn>

      <!-- Step 2 (regular outward) --> 
      <v-btn
        v-if="step == 2"
        color="secondary"
        @click="lResult.return ? step = 3 : carpool(0)"
      >
        {{ lResult.return ? $t('return') : $t('carpool') }}
      </v-btn>

      <!-- Step 3 (regular return) --> 
      <v-btn
        v-if="step == 3"
        color="secondary"
        @click="carpool(0)"
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
      returnSunTime: null
    }
  },
  computed: {
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
      this.computeTimes();
    }
  },
  mounted() {
    this.computeTimes();
  },
  methods: {
    computeMaxDate() {
      if (this.range==0) {
        this.maxDate = this.fromDate;
      } else if (this.range == 1) {
        this.maxDate = moment(this.fromDate).add(1, 'M').toISOString();
      } else if (this.range == 2) {
        this.maxDate = moment(this.fromDate).add(3, 'M').toISOString();
      }
    },
    computeTimes() {
      if (this.lResult.frequency == 2 && this.lResult.resultPassenger && !this.lResult.resultDriver) {  
        this.outwardMonTime = this.lResult.resultPassenger.outward.monTime ? moment.utc(this.lResult.resultPassenger.outward.monTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.outwardTueTime = this.lResult.resultPassenger.outward.tueTime ? moment.utc(this.lResult.resultPassenger.outward.tueTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.outwardWedTime = this.lResult.resultPassenger.outward.wedTime ? moment.utc(this.lResult.resultPassenger.outward.wedTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.outwardThuTime = this.lResult.resultPassenger.outward.thuTime ? moment.utc(this.lResult.resultPassenger.outward.thuTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.outwardFriTime = this.lResult.resultPassenger.outward.friTime ? moment.utc(this.lResult.resultPassenger.outward.friTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.outwardSatTime = this.lResult.resultPassenger.outward.satTime ? moment.utc(this.lResult.resultPassenger.outward.satTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.outwardSunTime = this.lResult.resultPassenger.outward.sunTime ? moment.utc(this.lResult.resultPassenger.outward.sunTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.returnMonTime = this.lResult.resultPassenger.return.monTime ? moment.utc(this.lResult.resultPassenger.return.monTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.returnTueTime = this.lResult.resultPassenger.return.tueTime ? moment.utc(this.lResult.resultPassenger.return.tueTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.returnWedTime = this.lResult.resultPassenger.return.wedTime ? moment.utc(this.lResult.resultPassenger.return.wedTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.returnThuTime = this.lResult.resultPassenger.return.thuTime ? moment.utc(this.lResult.resultPassenger.return.thuTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.returnFriTime = this.lResult.resultPassenger.return.friTime ? moment.utc(this.lResult.resultPassenger.return.friTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.returnSatTime = this.lResult.resultPassenger.return.satTime ? moment.utc(this.lResult.resultPassenger.return.satTime).format(this.$t('i18n.time.format.hourMinute')) : null;
        this.returnSunTime = this.lResult.resultPassenger.return.sunTime ? moment.utc(this.lResult.resultPassenger.return.sunTime).format(this.$t('i18n.time.format.hourMinute')) : null;
      // } else if (this.lResult.frequency == 2 && this.lResult.resultDriver && !this.lResult.resultPassenger) {  
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
      // if the requester can be passenger, we take the informations from the resultPassenger outward item
      if (this.lResult.resultPassenger) {
        params.proposalId = this.lResult.resultPassenger.outward.proposalId;
        params.origin = this.lResult.resultPassenger.outward.origin;
        params.destination = this.lResult.resultPassenger.outward.destination;
        params.date = this.lResult.resultPassenger.outward.date;
        params.time = this.lResult.resultPassenger.outward.time;
        params.priceKm = this.lResult.resultPassenger.outward.priceKm;
      }
      this.$emit('contact', params);
    },
    carpool(role) {
      if (this.lResult.frequency == 1) {
        // punctual => we send an event to the parent
        this.carpoolLoading = true;
        this.contactDisabled = true;
        let params = {
          "driver": this.lResult.resultDriver && role<2 ? true : false,
          "passenger": this.lResult.resultPassenger && role != 1 ? true : false,
          "regular" : this.lResult.frequency == 2
        };
        // if the requester can be passenger, we take the informations from the resultPassenger outward item
        if (this.lResult.resultPassenger) {
          params.proposalId = this.lResult.resultPassenger.outward.proposalId;
          params.origin = this.lResult.resultPassenger.outward.origin;
          params.destination = this.lResult.resultPassenger.outward.destination;
          params.date = this.lResult.resultPassenger.outward.date;
          params.time = this.lResult.resultPassenger.outward.time;
          params.priceKm = this.lResult.resultPassenger.outward.priceKm;
        }
        this.$emit('carpool', params);
      } else {
        // regular => we display the stepper to select outward, return (if relevant) and date range

      }
    },
    change() {
      this.computeMaxDate();
    }
  }
};
</script>
<style>
</style>
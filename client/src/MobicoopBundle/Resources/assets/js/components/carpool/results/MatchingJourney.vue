<template>
  <div>
    <v-card
      style="overflow:hidden"
    >
      <v-toolbar
        color="primary"
      >
        <v-toolbar-title class="toolbar">
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
            <!-- Journey details and carpooler -->
            <v-row dense>
              <v-col cols="12">
                <warning-message :fraud-warning-display="fraudWarningDisplay" />
              </v-col>
            </v-row>
            <v-row dense>
              <v-col cols="8">
                <!-- Journey Details -->


                <v-row dense>
                  <v-col cols="12">
                    <v-card-text>
                      <!-- Date / seats / price -->
                      <v-row
                        align="center"
                        dense
                      >
                        <!-- Date -->
                        <v-col
                          v-if="!regular"
                          cols="7"
                          class="text-h6 text-center"
                        >
                          {{ computedDate }}
                        </v-col>

                        <v-col
                          v-else
                          cols="7"
                          class="text-h6 text-center"
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
                          cols="2"
                          class="text-h6 text-center"
                        >
                          {{ $tc('places', lResult.seats, { seats: lResult.seats }) }}
                        </v-col>

                        <!-- Price -->
                        <v-col
                          cols="3"
                          class="text-h6 text-center"
                        >
                          {{ lResult.roundedPrice ? lResult.roundedPrice +'€' : '' }}
                          <v-tooltip
                            slot="append"
                            right
                            color="info"
                            :max-width="'35%'"
                          >
                            <template v-slot:activator="{ on }">
                              <v-icon
                                justify="left"
                                v-on="on"
                              >
                                mdi-help-circle-outline
                              </v-icon>
                            </template>
                            <span>{{ $t('priceTooltip') }}</span>
                          </v-tooltip>
                        </v-col>
                      </v-row>

                      <!-- Route / carpooler -->
                      <v-row
                        align="start"
                        dense
                      >
                        <!-- Route -->
                        <v-col
                          cols="12"
                        >
                          <v-row
                            v-if="lResult.noticeableDetour"
                            class="subtitle-2"
                            dense
                          >
                            <v-col v-if="lResult.role == 1">
                              <v-icon>mdi-clock</v-icon> {{ $t('detour.onlyDriver') }}
                            </v-col>
                            <v-col v-else>
                              <v-icon>mdi-clock</v-icon> {{ $t('detour.default') }}
                            </v-col>
                          </v-row>

                          <v-row dense>
                            <v-col>
                              <v-journey
                                :time="lResult.time || lResult.outwardTime ? true : false"
                                :waypoints="waypoints"
                                :noticeable-detour="lResult.noticeableDetour"
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
                      </v-row>
                    </v-card-text>
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="4">
                <!-- Carpooler -->
                <v-card
                  outlined
                >
                  <ProfileSummary
                    :user-id="result.carpooler.id"
                    :refresh="profileSummaryRefresh"
                    :age-display="ageDisplay"
                    :verified-identity="result.carpooler.verifiedIdentity"
                    :show-verified-identity="result.carpooler.verifiedIdentity !== null"
                    @showProfile="step=4"
                    @profileSummaryRefresh="refreshProfileSummary"
                  />
                  <v-card-text>
                    <v-row
                      dense
                    >
                      <v-col
                        cols="12"
                        class="text-center"
                      >
                        <v-btn
                          v-if="!hideContact && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false"
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
                        <!-- Carpool (driver xor passenger) -->
                        <v-btn
                          v-if="((driver ^ passenger) && step == 1 && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false) || (carpoolRoleSelected && step == 1)"
                          class="mt-4"
                          color="secondary"
                          :disabled="carpoolDisabled"
                          :loading="carpoolLoading"
                          @click="lResult.frequency == 1 ? (driver ? carpoolConfirm(1) : carpoolConfirm(2)) : step = 2"
                        >
                          {{ lResult.frequency == 1 ? $t('carpool') : $t('outward') }}
                        </v-btn>

                        <!-- Step 2 (regular outward, no return) -->
                        <v-btn
                          v-if="step == 2 && !lResult.return && outwardTrip.length>0 && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false"
                          color="secondary"
                          :disabled="carpoolDisabled"
                          :loading="carpoolLoading"
                          @click="carpoolRoleSelected ? carpoolConfirm(carpoolRoleSelected) : driver ? carpoolConfirm(1) : carpoolConfirm(2)"
                        >
                          {{ $t('carpool') }}
                        </v-btn>

                        <!-- Step 3 (regular return) -->
                        <v-btn
                          v-if="step == 3 && (outwardTrip.length > 0 || returnTrip.length>0) && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false"
                          color="secondary"
                          :disabled="carpoolDisabled"
                          :loading="carpoolLoading"
                          @click="carpoolRoleSelected ? carpoolConfirm(carpoolRoleSelected) : driver ? carpoolConfirm(1) : carpoolConfirm(2)"
                        >
                          {{ $t('carpool') }}
                        </v-btn>
                        <v-card
                          v-else
                          flat
                        >
                          <v-card-text
                            v-if="lResult.acceptedAsk"
                            class="success--text"
                          >
                            {{ $t('contactTips.acceptedAsk') }}
                          </v-card-text>
                          <v-card-text
                            v-else-if="lResult.pendingAsk"
                            class="warning--text"
                          >
                            {{ $t('contactTips.pendingAsk') }}
                          </v-card-text>
                          <v-card-text
                            v-else-if="lResult.initiatedAsk"
                            class="warning--text"
                          >
                            {{ $t('contactTips.initiatedAsk') }}
                          </v-card-text>
                        </v-card>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
            <!-- end Journey details and carpooler -->
            <!-- Map -->
            <v-row dense>
              <v-col cols="12">
                <m-map
                  ref="mmap"
                  type-map="community"
                  :points="pointsToMap"
                  :ways="directionWay"
                  :markers-draggable="false"
                  :clusters="false"
                  class="pa-4 mt-5"
                  :relay-points="true"
                />
              </v-col>
            </v-row>
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
                    first-day-of-week="1"
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
                    first-day-of-week="1"
                    @input="menuMaxDate = false"
                    @change="change"
                  />
                </v-menu>
              </v-col>
            </v-row>

            <regular-ask
              :type="1"
              :mon-check-default="monCheckDefault"
              :tue-check-default="tueCheckDefault"
              :wed-check-default="wedCheckDefault"
              :thu-check-default="thuCheckDefault"
              :fri-check-default="friCheckDefault"
              :sat-check-default="satCheckDefault"
              :sun-check-default="sunCheckDefault"
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
              :mon-check-default="monCheckDefault"
              :tue-check-default="tueCheckDefault"
              :wed-check-default="wedCheckDefault"
              :thu-check-default="thuCheckDefault"
              :fri-check-default="friCheckDefault"
              :sat-check-default="satCheckDefault"
              :sun-check-default="sunCheckDefault"
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

          <v-stepper-content step="4">
            <PublicProfile
              :user-id="result.carpooler.id"
              :refresh="refreshPublicProfile"
              :age-display="ageDisplay"
              :carpool-settings-display="carpoolSettingsDisplay"
              @publicProfileRefresh="publicProfileRefresh"
            />
          </v-stepper-content>
        </v-stepper-items>
      </v-stepper>

      <!-- Action buttons -->
      <v-card-actions
        v-if="(driver ^ passenger) || (driver && passenger)"
      >
        <v-spacer />
        <!-- Carpool regular(driver) -->
        <v-btn
          v-if="(lResult.return && step == 3 || !lResult.return && step == 2 ) && driver && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false && lResult.frequency == 2 && !carpoolRoleSelected"
          color="secondary"
          :disabled="carpoolDisabled"
          :loading="carpoolLoading"
          @click="carpoolConfirmRegular(1)"
        >
          {{ $t('carpoolAsDriver') }}
        </v-btn>

        <!-- Carpool regular(passenger) -->
        <v-btn
          v-if="(lResult.return && step == 3 || !lResult.return && step == 2 ) && !driver && passenger && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false && lResult.frequency == 2 && !carpoolRoleSelected"
          color="secondary"
          :disabled="carpoolDisabled"
          :loading="carpoolLoading"
          @click="carpoolConfirmRegular(2)"
        >
          {{ $t('carpoolAsPassenger') }}
        </v-btn>

        <!-- Carpool punctual(driver) -->
        <v-btn
          v-if="driver && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false && lResult.frequency == 1"
          color="secondary"
          :disabled="carpoolDisabled"
          :loading="carpoolLoading"
          @click="carpoolConfirm(1)"
        >
          {{ $t('carpoolAsDriver') }}
        </v-btn>

        <!-- Carpool punctual(passenger) -->
        <v-btn
          v-if="!driver && passenger && lResult.pendingAsk == false && lResult.acceptedAsk == false && lResult.initiatedAsk == false && lResult.frequency == 1"
          color="secondary"
          :disabled="carpoolDisabled"
          :loading="carpoolLoading"
          @click="carpoolConfirm(2)"
        >
          {{ $t('carpoolAsPassenger') }}
        </v-btn>

        <v-row>
          <!-- if an ask is pending or accepted -->
          <v-row
            v-if="lResult.pendingAsk == true || lResult.acceptedAsk == true"
          >
            <v-col
              cols="8"
              align-self="end"
              class="text-right"
            >
              <p class="warning--text font-weight-bold mb-n1">
                <v-icon color="warning">
                  mdi-alert
                </v-icon>
                {{ $t('alreadyAskCarpool') }}
              </p>
            </v-col>
          </v-row>

          <!-- if an ask is initiated -->
          <v-row
            v-if="lResult.initiatedAsk == true"
          >
            <v-col
              cols="8"
              align-self="end"
              class="text-right"
            >
              <p
                class="warning--text font-weight-bold mb-n1"
              >
                <v-icon color="warning">
                  mdi-alert
                </v-icon>
                {{ $t('alreadyInitiatedCarpool') }}
              </p>
            </v-col>
            <v-col
              cols="3"
              align-self="end"
              class="text-right"
            >
              <a
                :href="this.$t('seeMessages.route')"
                style="text-decoration:none;"
              >
                <v-btn
                  color="secondary"
                >
                  {{ $t('seeMessages.label') }}
                </v-btn>
              </a>
            </v-col>
          </v-row>
        </v-row>

        <!-- Step 2 or 3 (previous) -->

        <!-- Public profile -->
        <v-btn
          v-if="step == 4"
          color="secondary"
          outlined
          @click="step = 1"
        >
          {{ $t('previous') }}
        </v-btn>

        <v-btn
          v-else-if="step > 1"
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
      </v-card-actions>
    </v-card>

    <!-- confirm carpool dialog -->
    <v-dialog
      v-model="carpoolDialog"
      max-width="600"
    >
      <v-card>
        <v-card-title>
          {{ $t('confirmCarpoolTitle') }}
        </v-card-title>
        <v-card-text>
          {{ $t('confirmCarpool', { carpooler: lResult.carpooler.givenName }) }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="green darken-1"
            text
            @click="closeConfirmationDialog"
          >
            {{ $t('cancel') }}
          </v-btn>
          <v-btn
            color="primary"
            @click="carpool(carpoolRole)"
          >
            {{ $t('confirm') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import moment from "moment";
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/results/MatchingJourney/";
import VJourney from "@components/carpool/utilities/VJourney";
import RegularDaysSummary from "@components/carpool/utilities/RegularDaysSummary";
import RegularAsk from "@components/carpool/utilities/RegularAsk";
import ProfileSummary from "@components/user/profile/ProfileSummary";
import PublicProfile from "@components/user/profile/PublicProfile";
import MMap from "@components/utilities/MMap/MMap";
import WarningMessage from "@components/utilities/WarningMessage";
import L from "leaflet";

export default {
  components: {
    VJourney,
    RegularDaysSummary,
    RegularAsk,
    ProfileSummary,
    PublicProfile,
    MMap,
    WarningMessage
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props: {
    result: {
      type: Object,
      default: null
    },
    defaultStep: {
      type: Number,
      default: 1
    },
    defaultOutwardTrip: {
      type: Array,
      default: function(){return []}
    },
    defaultReturnTrip: {
      type: Array,
      default: function(){return []}
    },
    defaultOutwardMonTime: {
      type: String,
      default: null
    },
    defaultOutwardTueTime: {
      type: String,
      default: null
    },
    defaultOutwardWedTime: {
      type: String,
      default: null
    },
    defaultOutwardThuTime: {
      type: String,
      default: null
    },
    defaultOutwardFriTime: {
      type: String,
      default: null
    },
    defaultOutwardSatTime: {
      type: String,
      default: null
    },
    defaultOutwardSunTime: {
      type: String,
      default: null
    },
    defaultReturnMonTime: {
      type: String,
      default: null
    },
    defaultReturnTueTime: {
      type: String,
      default: null
    },
    defaultReturnWedTime: {
      type: String,
      default: null
    },
    defaultReturnThuTime: {
      type: String,
      default: null
    },
    defaultReturnFriTime: {
      type: String,
      default: null
    },
    defaultReturnSatTime: {
      type: String,
      default: null
    },
    defaultReturnSunTime: {
      type: String,
      default: null
    },
    defaultRole:{
      type: String,
      default:null
    },
    user: {
      type: Object,
      default: null
    },
    hideContact: {
      type: Boolean,
      default: false
    },
    resetStep: {
      type: Boolean,
      default: false
    },
    profileSummaryRefresh: {
      type: Boolean,
      default: false
    },
    fraudWarningDisplay: {
      type: Boolean,
      default: false
    },
    ageDisplay: {
      type: Boolean,
      default: false
    },
    refreshMap: {
      type: Boolean,
      default: false
    },
    carpoolSettingsDisplay: {
      type: Boolean,
      default: true
    }
  },
  data : function() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      lResult: this.result,
      contactLoading: false,
      carpoolLoading: false,
      contactDisabled: this.result.myOwn,
      carpoolDisabled: this.result.myOwn,
      step:this.defaultStep,
      fromDate: this.result.startDate ? this.result.startDate : null,
      menuFromDate: false,
      maxDate: this.result.startDate ? this.result.startDate : null,
      menuMaxDate: false,
      toDate: this.result.toDate ? this.result.toDate : null,
      range: 0,
      outwardMonTime: this.defaultOutwardMonTime,
      outwardTueTime: this.defaultOutwardTueTime,
      outwardWedTime: this.defaultOutwardWedTime,
      outwardThuTime: this.defaultOutwardThuTime,
      outwardFriTime: this.defaultOutwardFriTime,
      outwardSatTime: this.defaultOutwardSatTime,
      outwardSunTime: this.defaultOutwardSunTime,
      returnMonTime: this.defaultReturnMonTime,
      returnTueTime: this.defaultReturnTueTime,
      returnWedTime: this.defaultReturnWedTime,
      returnThuTime: this.defaultReturnThuTime,
      returnFriTime: this.defaultReturnFriTime,
      returnSatTime: this.defaultReturnSatTime,
      returnSunTime: this.defaultReturnSunTime,
      outwardTrip: this.defaultOutwardTrip,
      returnTrip: this.defaultReturnTrip,
      refreshPublicProfile: false,
      pointsToMap: [],
      relayPointsMap: [],
      directionWay: [],
      primaryColor: this.$vuetify.theme.themes.light.primary,
      secondaryColor: this.$vuetify.theme.themes.light.secondary,
      geoRouteUrl:"/georoute",
      route:[],
      routeRequester:[],
      routeCarpooler:[],
      carpoolDialog: false,
      carpoolRole: null,
      carpoolRoleSelected: null,
    }
  },
  computed: {
    today() {
      return moment().toISOString();
    },
    driver() {
      if(this.defaultRole){
        return (this.defaultRole == "driver");
      }
      else{
        return (this.lResult != null && this.lResult.resultDriver != null);
      }
    },
    passenger() {
      if(this.defaultRole){
        return (this.defaultRole=="passenger");
      }
      else{
        return (this.lResult != null && this.lResult.resultPassenger != null);
      }
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
      return this.fromDate
        ? moment(this.fromDate).format(this.$t("i18n.date.format.shortDate"))
        : "";
    },
    computedMaxDate() {
      return this.maxDate
        ? moment(this.maxDate).format(this.$t("i18n.date.format.shortDate"))
        : "";
    },
    age() {
      if (this.lResult) {
        if (this.lResult.carpooler.birthYear) {
          return moment().diff(moment([this.lResult.carpooler.birthYear]),'years')+' '+this.$t("birthYears");
        }
      }
      return null;
    },
    waypoints() {
      return this.lResult.resultPassenger ? this.lResult.resultPassenger.outward.waypoints : this.lResult.resultDriver.outward.waypoints;
    },
    monCheckDefault(){
      return this.checkDay("mon");
    },
    tueCheckDefault(){
      return this.checkDay("tue");
    },
    wedCheckDefault(){
      return this.checkDay("wed");
    },
    thuCheckDefault(){
      return this.checkDay("thu");
    },
    friCheckDefault(){
      return this.checkDay("fri");
    },
    satCheckDefault(){
      return this.checkDay("sat");
    },
    sunCheckDefault(){
      return this.checkDay("sun");
    }
  },
  watch: {
    result(val) {
      this.lResult = val;
      this.fromDate = val.startDate ? val.startDate : null;
      this.toDate = val.toDate ? val.toDate : null;
      this.contactDisabled = this.result.myOwn;
      this.carpoolDisabled = this.result.myOwn;
      this.computeTimes();
    },
    resetStep(){
      if(this.resetStep){
        this.step = 1;
        this.$emit('resetStepMatchingJourney');
      }
    },
    step(){
      if(this.step==4){
        this.refreshPublicProfile = true;
      } else if (this.step==1) {
        this.carpoolRoleSelected = null;
      }
    },
    route(){
      if(this.route.directPoints) this.buildJourney(this.route);
    },
    routeCarpooler(){
      if(this.routeCarpooler.directPoints) this.buildJourney(this.routeCarpooler, false);
    },
    routeRequester(){
      if(this.routeRequester.directPoints) this.buildJourney(this.routeRequester, true);
    },
    refreshMap(){
      if(this.refreshMap){
        this.cleanMap();
        this.getRoute();
        this.buildMarkers();
      }
    }
  },
  mounted() {
    this.computeMaxDate();
    this.computeTimes();
    this.getRoute();
    this.buildMarkers();
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    // We need to emit this event because the first time Matching has send the reset intruction,
    // MatchingJourney was not created yet. So the watcher can't trigger and therefore, not send the event.
    this.$emit('resetStepMatchingJourney');
  },
  methods: {
    closeConfirmationDialog() {
      this.carpoolDialog = false;

      if (this.carpoolRoleSelected) {
        this.carpoolRoleSelected = null;
      }
    },
    computeMaxDate() {
      if (this.range == 0) {
        this.maxDate = moment(this.fromDate).add(1, 'W').toISOString();
      }
      else if (this.range == 1) {
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
      } else if (this.lResult.frequency == 2 && this.lResult.resultDriver && this.lResult.resultPassenger && this.carpoolRoleSelected == 1) {
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
      } else if (this.lResult.frequency == 2 && this.lResult.resultDriver && this.lResult.resultPassenger && this.carpoolRoleSelected == 2) {
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
      if (this.lResult.resultPassenger) {
        resultChoice = this.lResult.resultPassenger;
      } else {
        resultChoice = this.lResult.resultDriver;
      }
      // proposal and matching results
      params.adIdResult = resultChoice.outward.proposalId;
      params.matchingId = resultChoice.outward.matchingId;
      params.date = resultChoice.outward.date;
      params.time = resultChoice.outward.time;

      // These infos are necessary to generay a non persisted message thread
      params.idRecipient = this.lResult.carpooler.id;
      params.shortFamilyName = this.lResult.carpooler.shortFamilyName;
      params.givenName = this.lResult.carpooler.givenName;
      params.avatar = this.lResult.carpooler.avatars[0];
      params.carpoolInfos = {
        askHistoryId: null,
        origin: this.lResult.origin.addressLocality,
        destination: this.lResult.destination.addressLocality,
        criteria: {
          frequency:this.lResult.frequency,
          fromDate:this.lResult.frequency==1 ? this.lResult.date : this.lResult.startDate,
          fromTime:this.lResult.time,
          monCheck:this.lResult.monCheck,
          tueCheck:this.lResult.tueCheck,
          wedCheck:this.lResult.wedCheck,
          thuCheck:this.lResult.thuCheck,
          friCheck:this.lResult.friCheck,
          satCheck:this.lResult.satCheck,
          sunCheck:this.lResult.sunCheck
        }
      };
      this.$emit('contact', params);
    },
    carpoolConfirm(role) {
      this.carpoolRole = role;
      this.carpoolDialog = true;
    },
    carpoolConfirmRegular(role) {
      this.carpoolRoleSelected = role;
      this.carpoolRole = role;
      this.computeTimes();
      this.carpoolDialog = true;
    },
    carpool(role) {
      this.carpoolDialog = false;
      this.carpoolLoading = true;
      this.contactDisabled = true;
      let params = {
        "driver": role==1,
        "passenger": role==2,
        "regular": this.lResult.frequency == 2,
        "status" : this.lResult.askStatus
      };
      let resultChoice = this.lResult.resultDriver;
      if (role == 2) resultChoice = this.lResult.resultPassenger;
      if (this.lResult.frequency == 2) {
        params.outwardSchedule = this.getDays(this.outwardTrip);
        params.returnSchedule = this.getDays(this.returnTrip);
        params.fromDate = this.fromDate ? moment(this.fromDate).format(this.$t('i18n.date.format.computeDate')) : null;
        params.toDate = this.maxDate ? moment(this.maxDate).format(this.$t('i18n.date.format.computeDate')) : null;
      } else {
        params.date = resultChoice.outward.date;
        params.time = resultChoice.outward.time;
      }
      // proposal and matching results
      params.adId = resultChoice.outward.proposalId;
      params.matchingId = resultChoice.outward.matchingId;
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

      if(trip.length==0) return null;

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
        if (trip[i].day == "mon") days.monTime = (trip[i].time) ? trip[i].time.replace("h",":") : null;
        if (trip[i].day == "tue") days.tueTime = (trip[i].time) ? trip[i].time.replace("h",":") : null;
        if (trip[i].day == "wed") days.wedTime = (trip[i].time) ? trip[i].time.replace("h",":") : null;
        if (trip[i].day == "thu") days.thuTime = (trip[i].time) ? trip[i].time.replace("h",":") : null;
        if (trip[i].day == "fri") days.friTime = (trip[i].time) ? trip[i].time.replace("h",":") : null;
        if (trip[i].day == "sat") days.satTime = (trip[i].time) ? trip[i].time.replace("h",":") : null;
        if (trip[i].day == "sun") days.sunTime = (trip[i].time) ? trip[i].time.replace("h",":") : null;
      }
      return days;
    },
    checkDay(day){
      let found = false;
      this.outwardTrip.forEach((currentDay, index) => {
        if(currentDay.day==day){
          found = true;
        }
      });
      return found;
    },
    publicProfileRefresh(data){
      this.refreshPublicProfile = false;
    },
    refreshProfileSummary(data){
      this.$emit("profileSummaryRefresh",data);
    },
    getIcon(type,role) {
      if (role == 'driver') {
        if (type == 'origin') return "/images/cartography/pictos/home.svg";
        if (type == 'destination') return "/images/cartography/pictos/flag.svg";
        if (type == 'step') return "";
      } else {
        if (type == 'origin') return "pickup";
        if (type == 'destination') return "dropoff";
        if (type == 'step') return "";
      }
    },
    buildMarkers(){
      this.waypoints.forEach((waypoint, index) => {

        // Determine the icon
        let icon = this.getIcon(waypoint.type,waypoint.role);

        this.pointsToMap.push(this.buildPoint(waypoint.address.latitude,waypoint.address.longitude,"",icon,[36, 42],[10, 25]));
      });
      this.$refs.mmap.redrawMap();
    },
    getColorCircleMarker(){
      return this.primaryColor;
    },
    getColorJourney(requester = null){
      if(requester == null){
        if(this.lResult.role == 1 || this.lResult.role == 2){
          return this.secondaryColor;
        }
      }
      else if(requester){
        return this.primaryColor;
      }
      else{
        return this.secondaryColor;
      }
      return this.primaryColor;
    },
    buildJourney(route, requester = null){
      let currentProposal = {
        latLngs:route.directPoints,
        color:this.getColorJourney(requester),
        dashArray:(requester !== null && requester) ? '12' : null
      };

      this.directionWay.push(currentProposal);
      this.$refs.mmap.redrawMap();
      this.$emit('mapRefreshed');
    },
    buildPoint: function(
      lat,
      lng,
      title = "",
      pictoUrl = "",
      size = [],
      anchor = [],
      popupDesc = ""
    ) {
      let point = {
        title: title,
        latLng: L.latLng(lat, lng),
        icon: {},
      };

      if(pictoUrl == "pickup" || pictoUrl == "dropoff"){
        point.circleMarker = true;
        point.color = this.getColorCircleMarker();
      }

      if (pictoUrl !== "" && !point.circleMarker) {
        point.icon = {
          url: pictoUrl,
          size: size,
          anchor: anchor,
        };
      }

      if (popupDesc !== "") {
        point.popup = {
          title: title,
          description: popupDesc,
        };
      }

      return point;
    },
    getRoute() {

      if(this.lResult.role == 3){

        let paramsRequester = "?";
        let paramsCarpooler = "?";
        let nbWaypointsRequester = 0;
        let nbWaypointsCarpooler = 0;
        this.waypoints.forEach((item,key) => {
          if (item.address) {
            if(item.person=="carpooler"){
              nbWaypointsCarpooler++;
              paramsCarpooler += `&points[${nbWaypointsCarpooler}][longitude]=${item.address.longitude}&points[${nbWaypointsCarpooler}][latitude]=${item.address.latitude}`;
            }
            else{
              nbWaypointsRequester++;
              paramsRequester += `&points[${nbWaypointsRequester}][longitude]=${item.address.longitude}&points[${nbWaypointsRequester}][latitude]=${item.address.latitude}`;
            }
          }
        });

        this.callSig(paramsCarpooler,false);
        this.callSig(paramsRequester,true);

      }
      else{
        let params = "?";
        let nbWaypoints = 0;
        this.waypoints.forEach((item,key) => {
          if (item.address) {
            nbWaypoints++;
            params += `&points[${nbWaypoints}][longitude]=${item.address.longitude}&points[${nbWaypoints}][latitude]=${item.address.latitude}`;
          }
        });
        this.callSig(params);
        nbWaypoints++;
      }

    },
    callSig(params, requester = null){
      maxios
        .get(`${this.geoRouteUrl}${params}`)
        .then(res => {
          if(requester == null){
            this.route = res.data.member[0];
          }
          else if(requester){
            this.routeRequester = res.data.member[0];
          }
          else{
            this.routeCarpooler = res.data.member[0];
          }
        })
        .catch(err => {
          console.error(err);
        });
    },
    cleanMap(){
      this.route = [];
      this.routeCarpooler = [];
      this.routeRequester = [];
      this.pointsToMap = [];
      this.relayPointsMap = [];
      this.directionWay = [];
      this.$refs.mmap.redrawMap();
    }

  },
};
</script>
<style lang="scss" scoped>
.toolbar{
    color: #fff;
}
</style>

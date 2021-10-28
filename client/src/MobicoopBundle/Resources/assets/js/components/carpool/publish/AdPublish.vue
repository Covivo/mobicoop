<template>
  <v-container fluid>
    <!--prevent user to update data before full initialization-->
    <v-row
      v-if="isValidUpdate && !bodyIsFullyLoaded"
      id="loading-screen"
      justify="center"
      align="center"
    >
      <div class="text-center">
        <v-progress-circular
          :indeterminate="true"
          :rotate="0"
          :size="48"
          color="primary"
        />
      </div>
    </v-row>
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      top
    >
      {{ snackbar.message }}
      <v-btn
        color="white"
        text
        @click="snackbar.show = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-snackbar
      v-model="snackErrorPublish.show"
      :color="snackErrorPublish.color"
      top
      timeout="-1"
    >
      {{ snackErrorPublish.message }}
      <v-btn
        color="white"
        text
        @click="snackErrorPublish.show = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>    

    <!-- Title and subtitle -->
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        xl="8"
        align="center"
      >
        <h1>{{ $t( isUpdate && !isSearchToSave ? 'update.title' : 'create.title') }}</h1>
        <h3 style="height: 30px">
          {{ step === 1 && !isUpdate ? $t('create.subtitle') : "" }}
        </h3>
      </v-col>
    </v-row>
    <v-row
      v-if="solidaryExclusiveAd"
      justify="center"
    >
      <v-col
        cols="12"
        xl="8"
      >
        <v-alert type="info">
          <p>{{ $t("messageSolidaryExclusiveAd.message") }}</p>
        </v-alert>
      </v-col>
    </v-row>
    <v-row
      v-if="solidaryExclusiveAd"
      justify="center"
    >
      <v-col
        cols="12"
        xl="8"
        class="d-flex justify-center"
      >
        <v-switch
          v-model="solidaryExclusive"
          color="success"
          inset
          :label="$t('messageSolidaryExclusiveAd.switch.label')"
        />
      </v-col>
    </v-row>
    <v-row
      v-if="firstAd"
      justify="center"
    >
      <v-col
        cols="12"
        xl="8"
      >
        <v-alert type="info">
          <p>{{ $t("messageFirstAd.signUpDone", {'givenName':user.givenName}) }}.</p>
          <p>{{ $t("messageFirstAd.alert") }}</p>
        </v-alert>
      </v-col>
    </v-row>
    <v-row
      v-if="isSearchToSave"
      justify="center"
    >
      <v-col
        cols="12"
        xl="8"
      >
        <v-alert type="info">
          <p>{{ $t("searchToSave.messageTitle") }}</p>
          <p>{{ $t("searchToSave.message") }}</p>
        </v-alert>
      </v-col>
    </v-row>
    <!-- Stepper -->
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        xl="8"
        align="center"
      >
        <v-stepper
          v-model="step"
          alt-labels
          class="elevation-0"
        >
          <!-- Stepper Header -->
          <v-stepper-header
            v-show="step!==1 || isUpdate"
            class="elevation-0"
          >
            <!-- Step 1 : search journey -->
            <v-stepper-step
              complete
              editable
              :step="1"
              color="primary"
            >
              {{ $t('stepper.header.search_journey') }}
            </v-stepper-step>
            <v-divider />

            <!-- Step 2 : planification -->
            <v-stepper-step
              editable
              :step="2"
              color="primary"
            >
              {{ $t('stepper.header.planification') }}
            </v-stepper-step>
            <v-divider />

            <!-- Step 3 : map -->
            <v-stepper-step
              editable
              :step="3"
              color="primary"
            >
              {{ $t('stepper.header.map') }}
            </v-stepper-step>
            <v-divider />

            <!-- Step 4 : passengers (if driver) -->
            <v-stepper-step
              v-if="driver"
              editable
              :step="4"
              color="primary"
            >
              {{ $t('stepper.header.passengers') }}
            </v-stepper-step>
            <v-divider />

            <!-- Step 5 : participation (if driver) -->
            <v-stepper-step
              v-if="driver && !solidaryExclusive"
              editable
              :step="5"
              color="primary"
            >
              {{ $t('stepper.header.participation') }}
            </v-stepper-step>
            <v-divider />

            <!-- Step 6 : message -->
            <v-stepper-step
              editable
              :step="driver ? (solidaryExclusive ? 5 : 6) : 4"
              color="primary"
            >
              {{ $t('stepper.header.message') }}
            </v-stepper-step>
            <v-divider />

            <!-- Step 7 : summary -->
            <v-stepper-step
              color="primary"
              editable
              :step="driver ? (solidaryExclusive ? 6 : 7) : 5"
            >
              {{ $t('stepper.header.summary') }}
            </v-stepper-step>
          </v-stepper-header>

          <!-- Stepper Content -->
          <v-stepper-items>
            <!-- Step 1 : search journey -->
            <v-stepper-content step="1">
              <search-journey
                :solidary-exclusive-ad="solidaryExclusive"
                display-roles
                :geo-search-url="geoSearchUrl"
                :user="user"
                :init-outward-date="outwardDate"
                :init-origin="origin"
                :init-destination="destination"
                :init-regular="regular"
                :init-role="role"
                @change="searchChanged"
              />
            </v-stepper-content>

            <!-- Step 2 : planification -->
            <v-stepper-content step="2">
              <ad-planification
                :init-outward-date="outwardDate"
                :init-outward-time="outwardTime"
                :init-return-date="returnDate"
                :init-return-time="returnTime"
                :regular="regular"
                :default-margin-duration="defaultMarginDuration"
                :default-time-precision="defaultTimePrecision"
                :init-schedule="initSchedule"
                :route="route"
                @change="planificationChanged"
              />
            </v-stepper-content>

            <!-- Step 3 : route -->
            <v-stepper-content step="3">
              <v-row>
                <v-col cols="12">
                  <ad-route
                    :geo-search-url="geoSearchUrl"
                    :geo-route-url="geoRouteUrl"
                    :user="user"
                    :init-origin="origin"
                    :init-destination="destination"
                    :init-waypoints="initWaypoints"
                    :community-ids="communityIds"
                    @change="routeChanged"
                  />
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12">
                  <m-map
                    ref="mmapRoute"
                    type-map="adSummary"
                    :points="pointsToMap"
                    :ways="directionWay"
                    :provider="mapProvider"
                    :url-tiles="urlTiles"
                    :attribution-copyright="attributionCopyright"
                  />
                </v-col>
              </v-row>
            </v-stepper-content>

            <!-- Step 4 : passengers (if driver) -->
            <v-stepper-content
              v-if="driver"
              step="4"
            >
              <v-row
                dense
                align="center"
                justify="center"
              >
                <v-col
                  cols="3"
                  align="right"
                >
                  {{ $t('stepper.content.passengers.seats.question') }}
                </v-col>

                <v-col
                  cols="1"
                >
                  <v-select
                    v-model="seats"
                    :items="numberSeats"
                    item-text="text"
                    item-value="value"
                  />
                </v-col>

                <v-col
                  cols="2"
                  align="left"
                >
                  {{ $t('stepper.content.passengers.seats.passengers') }}
                </v-col>
              </v-row>

              <v-row
                align="center"
                dense
              >
                <v-col
                  cols="5"
                  offset="3"
                  align="left"
                >
                  {{ $t('stepper.content.passengers.luggage.label') }}
                </v-col>
                <v-col
                  cols="1"
                >
                  <v-switch
                    v-model="luggage"
                    inset
                    hide-details
                    class="mt-0 mb-1"
                    color="primary"
                  />
                </v-col>
                <v-col
                  cols="1"
                  align="left"
                >
                  <v-tooltip
                    right
                    color="info"
                  >
                    <template v-slot:activator="{ on }">
                      <v-icon v-on="on">
                        mdi-help-circle-outline
                      </v-icon>
                    </template>
                    <span>{{ $t('stepper.content.passengers.luggage.help') }}</span>
                  </v-tooltip>
                </v-col>
              </v-row>

              <v-row
                align="center"
                dense
              >
                <v-col
                  cols="5"
                  offset="3"
                  align="left"
                >
                  {{ $t('stepper.content.passengers.bike.label') }}
                </v-col>
                <v-col
                  cols="1"
                >
                  <v-switch
                    v-model="bike"
                    inset
                    hide-details
                    class="mt-0 mb-1"
                    color="primary"
                  />
                </v-col>
                <v-col
                  cols="1"
                  align="left"
                >
                  <v-tooltip
                    right
                    color="info"
                  >
                    <template v-slot:activator="{ on }">
                      <v-icon v-on="on">
                        mdi-help-circle-outline
                      </v-icon>
                    </template>
                    <span>{{ $t('stepper.content.passengers.bike.help') }}</span>
                  </v-tooltip>
                </v-col>
              </v-row>

              <v-row
                align="center"
                dense
              >
                <v-col
                  cols="5"
                  offset="3"
                  align="left"
                >
                  {{ $t('stepper.content.passengers.backSeats.label') }}
                </v-col>
                <v-col
                  cols="1"
                >
                  <v-switch
                    v-model="backSeats"
                    inset
                    hide-details
                    class="mt-0 mb-1"
                    color="primary"
                  />
                </v-col>
                <v-col
                  cols="1"
                  align="left"
                >
                  <v-tooltip
                    color="info"
                    right
                  >
                    <template v-slot:activator="{ on }">
                      <v-icon v-on="on">
                        mdi-help-circle-outline
                      </v-icon>
                    </template>
                    <span>{{ $t('stepper.content.passengers.backSeats.help') }}</span>
                  </v-tooltip>
                </v-col>
              </v-row>
            </v-stepper-content>

            <!-- Step 5 : participation (if driver) -->
            <v-stepper-content
              v-if="driver && !solidaryExclusive"
              step="5"
            >
              <v-row
                dense
                align="center"
                justify="center"
              >
                <v-col
                  cols="3"
                  align="right"
                >
                  {{ $t('stepper.content.participation.price') }}
                </v-col>

                <v-col
                  cols="2"
                >
                  <v-text-field
                    v-model="price"
                    :disabled="distance<=0"
                    type="number"
                    suffix="€"
                    :hint="hintPricePerKm"
                    persistent-hint
                    :color="colorPricePerKm"
                    :class="colorPricePerKm + '--text'"
                    @blur="roundPrice(price, regular ? 2 : 1, true)"
                    @change="disableNextButton = true;price = Math.abs(price)"
                  />
                </v-col>

                <v-col
                  cols="2"
                  align="left"
                >
                  {{ $t('stepper.content.participation.passengers') }}
                </v-col>
              </v-row>
              <v-row
                v-if="pricePerKm >= pricesRanges.mid"
                justify="center"
              >
                <v-col cols="10">
                  <v-card>
                    <v-card-text>
                      <p
                        v-if="pricePerKm >= pricesRanges.forbidden"
                        :class="colorPricePerKm + '--text'"
                      >
                        {{ $t('participation.forbidden') }}
                      </p>
                      <p
                        v-else-if="pricePerKm >= pricesRanges.high"
                        :class="colorPricePerKm + '--text'"
                      >
                        {{ $t('participation.high') }}
                      </p>
                      <p
                        v-else-if="pricePerKm >= pricesRanges.mid"
                        :class="colorPricePerKm + '--text'"
                      >
                        {{ $t('participation.mid') }}
                      </p>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
              <v-row justify="center">
                <v-col 
                  v-if="participationText"
                  cols="10"
                  align="center"
                >
                  <p
                    class="text-caption"
                    v-html="$t('participation.text')"
                  />
                </v-col>
              </v-row>
            </v-stepper-content>

            <!-- Step 6 : message -->
            <v-stepper-content
              :step="driver ? (solidaryExclusive ? 5 : 6) : 4"
            >
              <v-row
                dense
                align="center"
                justify="center"
              >
                <v-col
                  cols="6"
                >
                  <p v-if="driver && passenger">
                    {{ $t('stepper.content.message.title.both') }}
                  </p>
                  <p v-else-if="driver">
                    {{ $t('stepper.content.message.title.driver') }}
                  </p>
                  <p v-else>
                    {{ $t('stepper.content.message.title.passenger') }}
                  </p>
                  <v-textarea
                    v-model="message"
                    :label="$t('stepper.content.message.label')"
                  />
                </v-col>
              </v-row>
            </v-stepper-content>

            <!-- Step 7 : summary -->
            <v-stepper-content
              :step="driver ? (solidaryExclusive ? 6 : 7) : 5"
            >
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <ad-summary
                      :driver="driver"
                      :passenger="passenger"
                      :regular="regular"
                      :outward-date="outwardDate"
                      :outward-time="outwardTime"
                      :return-date="returnDate"
                      :return-time="returnTime"
                      :schedules="schedules"
                      :seats="seats"
                      :price="parseFloat(price)"
                      :route="route"
                      :message="message"
                      :user="user"
                      :origin="origin"
                      :destination="destination"
                      :solidary-exclusive="solidaryExclusive"
                      :age-display="ageDisplay"
                    />
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12">
                    <m-map
                      ref="mmapSummary"
                      type-map="adSummary"
                      :points="pointsToMap"
                      :ways="directionWay"
                      :provider="mapProvider"
                      :url-tiles="urlTiles"
                      :attribution-copyright="attributionCopyright"
                    />
                  </v-col>
                </v-row>

                <v-row>
                  <v-col
                    v-if="driver"
                  >
                    {{ $t('stepper.driverLicense.text') }}
                    <a
                      class="primary--text"
                      target="_blank"
                      :href="$t('stepper.driverLicense.route')"
                      @click.stop
                    >{{ $t('stepper.driverLicense.link') }}
                    </a>
                  </v-col>
                </v-row>
              </v-container>
            </v-stepper-content>
          </v-stepper-items>
        </v-stepper>
      </v-col>
    </v-row>
    <!-- </v-stepper-content> -->

    <!-- Buttons Previous and Next step -->
    <v-row
      mt-5
      justify="center"
    >
      <v-btn
        v-if="step > 1"
        rounded
        outlined
        color="secondary"
        align-center
        style="margin-bottom: 30px;"
        @click="--step"
      >
        {{ $t('stepper.buttons.previous') }}
      </v-btn>

      <v-btn
        v-if="(step === 5 && driver && !solidaryExclusive)"
        :disabled="disableNextButton || price < 0"
        :loading="loadingPrice"
        rounded
        color="secondary"
        align-center
        style="margin-left: 30px;"
        @click="step++"
      >
        {{ $t('stepper.buttons.next') }}
      </v-btn>

      <v-btn
        v-if="((step < 7 && driver && step !== 5 && !solidaryExclusive) || (step < 5 && !driver && !solidaryExclusive) || (solidaryExclusive && step < 6))"
        :disabled="!validNext"
        rounded
        color="secondary"
        align-center
        style="margin-left: 30px;"
        @click="step++"
      >
        {{ $t('stepper.buttons.next') }}
      </v-btn>

      <v-tooltip
        v-if="valid"
        bottom
      >
        <template v-slot:activator="{on}">
          <div v-on="(!valid)?on:{}">
            <v-btn
              :disabled="!valid || loading"
              :loading="loading"
              rounded
              color="secondary"
              style="margin-left: 30px;"
              align-center
              @click="isUpdate ? (hasAsks || hasPotentialAds ? dialog = true : updateAd()) : postAd()"
            >
              {{ isUpdate && !isSearchToSave ? $t('stepper.buttons.update_ad', {id: ad.id}) : $t('stepper.buttons.publish_ad') }}
            </v-btn>
          </div>
        </template>
        <span>{{ $t('stepper.buttons.notValid') }}</span>
      </v-tooltip>
    </v-row>

    <!-- DIALOG -->
    <v-row justify="center">
      <v-dialog
        v-model="dialog"
        persistent
        max-width="550"
      >
        <v-card>
          <v-card-title
            class="text-h5"
            v-html="popupTitle"
          />
          <v-card-text v-html="popupContent" />
          <v-container>
            <v-textarea
              v-if="isMajorUpdate && hasAsks"
              v-model="cancellationMessage"
            />
          </v-container>

          <v-card-actions>
            <v-spacer />
            <v-btn
              color="secondary"
              outlined
              @click="dialog = false"
            >
              {{ $t('no') }}
            </v-btn>
            <v-btn
              color="secondary"
              @click="updateAd"
            >
              {{ $t('yes') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>
  </v-container>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/publish/AdPublish/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/carpool/publish/AdPublish/";

import maxios from "@utils/maxios";
import { merge, isEmpty, isEqual } from "lodash";
import moment from 'moment';

import SearchJourney from "@components/carpool/search/SearchJourney";
import AdPlanification from "@components/carpool/publish/AdPlanification";
import AdRoute from "@components/carpool/publish/AdRoute";
import AdSummary from "@components/carpool/publish/AdSummary";
import MMap from '@components/utilities/MMap/MMap'
import L from "leaflet";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    },
  },
  components: {
    SearchJourney,
    AdPlanification,
    AdRoute,
    AdSummary,
    MMap
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    geoRouteUrl: {
      type: String,
      default: "/georoute"
    },
    user: {
      type: Object,
      default: null
    },
    defaultPriceKm: {
      type: Number,
      default: 0.06
    },
    defaultMarginDuration: {
      type: Number,
      default: null
    },
    mapProvider:{
      type: String,
      default: ""
    },
    urlTiles:{
      type: String,
      default: ""
    },
    attributionCopyright:{
      type: String,
      default: ""
    },
    communityIds: {
      type: Array,
      default: null
    },
    initOrigin: {
      type: Object,
      default: null
    },
    initDestination: {
      type: Object,
      default: null
    },
    initRegular: {
      type: Boolean,
      default: true
    },
    initDate: {
      type: String,
      default: null
    },
    initTime: {
      type: String,
      default: null
    },
    firstAd: {
      type: Boolean,
      default: false
    },
    solidaryExclusiveAd: {
      type: Boolean,
      default: false
    },
    defaultPricesRanges:{
      type: Object,
      default: null
    },
    ad: {
      type: Object,
      default: null
    },
    isUpdate: {
      type: Boolean,
      default: false
    },
    isSearchToSave: {
      type: Boolean,
      default: false
    },
    // for minor and major update popup
    hasAsks: {
      type: Boolean,
      default: false
    },
    // for major update popup only
    hasPotentialAds: {
      type: Boolean,
      default: false
    },
    defaultTimePrecision: {
      type: Number,
      default: null
    },
    participationText: {
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
      distance: 0, 
      duration: 0,
      outwardDate: this.initDate,
      outwardTime: this.initTime,
      returnDate: null,
      returnTime: null,
      origin: this.initOrigin,
      destination: this.initDestination,
      regular: this.initRegular,
      step:1,
      driver: true,
      passenger: true,
      luggage: false,
      bike: false,
      backSeats: false,
      schedules: null,
      returnTrip:null,
      route: null,
      price: null,
      pricePerKm: this.isUpdate && this.ad ? this.ad.priceKm : this.defaultPriceKm,
      message: null,
      baseUrl: window.location.origin,
      loading: false,
      loadingPrice: false,
      disableNextButton: false,
      userDelegated: null, // if user delegation
      selectedCommunities: null,
      pointsToMap:[],
      directionWay:[],
      bbox:null,
      regularLifeTime: null,    // not used yet
      strictDate: null,         // not used yet
      strictRegular: null,      // not used yet
      strictPunctual: null,     // not used yet
      useTime: null,            // not used yet
      anyRouteAsPassenger: null, // not used yet
      solidaryExclusive: this.solidaryExclusiveAd,
      numberSeats : [ 1,2,3,4],
      seats : 3,
      snackbar: {
        show: false,
        message: "",
        color: "success"
      },
      snackErrorPublish: {
        show: false,
        message: this.$t('snackBarErrorPublish'),
        color:"error"
      },
      priceForbidden: false,
      returnTimeIsValid: true,
      initWaypoints: [],
      initWaypointsCount: this.countWaypoints(),
      initSchedule: null,
      role: null,
      dialog: false,
      oldUpdateObject: null,
      cancellationMessage: "",
      bodyIsFullyLoaded: false
    }
  },
  computed: {
    pricesRanges(){
      if(this.defaultPricesRanges){
        return this.defaultPricesRanges;
      }
      else{
        return {
          "mid":0.12,
          "high":0.3,
          "forbidden":0.5
        }
      }
    },
    hintPricePerKm() {
      let pricePerKm = this.pricePerKm;
      if (isNaN(this.pricePerKm)) pricePerKm = 0;
      return pricePerKm.toFixed(2)+'€/km';
    },
    validWaypoints() {
      if (this.route && this.route.waypoints) {
        return this.route.waypoints.filter(function(waypoint) {
          return waypoint.visible && waypoint.address;
        });
      }
      return null;
    },
    valid() {
      // For the publish button

      // step validation
      if(this.solidaryExclusive){
        if(this.step<6) return false;
      }
      else{
        if ((this.driver && this.step != 7) || (!this.driver && this.step != 5)) return false;
      }
      // role validation
      if (this.driver === false && this.passenger === false) return false;
      // route validation
      if (this.distance<=0 || this.duration<=0 || !this.origin || !this.destination || !this.route) return false;
      // punctual date validation
      if (!this.regular && !(this.outwardDate && this.outwardTime)) return false;
      // punctual roundtrip date validation
      if (!this.regular && this.returnTrip && !(this.returnDate && this.returnTime)) return false;
      // regular date validation
      if (this.regular && !this.schedules) return false;
      // regular schedules validation
      if(this.step==2 && this.regular && (this.schedules==null || this.schedules.length==0)) return false;

      // Step 2 regular schedule no return without outward
      if(this.regular && this.schedules){
        let outwardSchedulesValid = true;
        this.schedules.forEach((s, index) => {
          if(s.returnTime !== null && s.outwardTime == null){
            outwardSchedulesValid = false;
          }        
        });
        return outwardSchedulesValid;
      }

      // Price to high. Forbidden to post
      if(this.priceForbidden) return false;
      // We are in update mode and initialization is not finished yet
      if (this.isValidUpdate && this.oldUpdateObject == null) return false;
      // update mode and there are no changes
      if (!this.isUpdated ) return false;

      // validation ok
      return true;
    },
    validNext() {

      // For the next button
      if(this.origin == null || this.destination == null) return false;
      if(!this.passenger && !this.driver) return false;
      if(!this.regular && !this.outwardDate) return false;
      if(!this.driver && this.step>4) return false;
      if(this.step>=7) return false;

      // Specifics by steps
      // Step 2 regular : you have to setup at least one schedule
      if(this.step==2 && this.regular && (this.schedules==null || this.schedules.length==0)) return false;

      // Step 2 regular schedule no return without outward
      if(this.step==2 && this.regular && this.schedules){
        let outwardSchedulesValid = true;
        this.schedules.forEach((s, index) => {
          if(s.returnTime !== null && s.outwardTime == null){
            outwardSchedulesValid = false;
          }        
        });
        return outwardSchedulesValid;
      }

      //We get here if we give at least the departure time on the 1st day
      //So now we can check on all others days, if visible and date AND at least 1 hour is not defined -> return false
      if(this.step ==2  && this.regular){
        for (var s in this.fullschedule) {
          var i = this.fullschedule[s];
          if (i.visible) {
            if ( !i.mon && !i.tue && !i.wed && !i.thu && !i.fri && !i.sat && !i.sun )  return false;
            if ( i.outwardTime == null && i.returnTime == null) return false;
          }
        }
      }

      // Step 2 punctual : you have to set the outward time
      if(this.step==2 && ((!this.regular && !(this.outwardDate && this.outwardTime)) || this.returnTimeIsValid === false)) return false;
      // Step 2 punctual, round-trip chosen : you have to set the outward date & time
      if(this.step==2 && !this.regular && this.returnTrip && !(this.returnDate && this.returnTime)) return false;

      return true;
    },
    pointEscapedPrice(){
      return this.price.replace(".",",");
    },
    colorPricePerKm(){
      if (this.pricePerKm < this.pricesRanges.mid) {
        return "success";
      } else if (this.pricePerKm >= this.pricesRanges.mid && this.pricePerKm < this.pricesRanges.high) {
        return "warning";
      } else {
        return "error";
      }
    },
    isValidUpdate () {
      return this.isUpdate && !isEmpty(this.ad);
    },
    isUpdated () {
      if (!this.isUpdate) return true;
      else return this.isValidUpdate && !isEqual(this.oldUpdateObject, this.newUpdateObject);
    },
    isMajorUpdate () {
      if (!this.isValidUpdate || isEmpty(this.oldUpdateObject)) return false;
      let newUpdateObject = this.newUpdateObject;
      return newUpdateObject.regular !== this.oldUpdateObject.regular
        || this.oldUpdateObject.driver !== newUpdateObject.driver
        || this.oldUpdateObject.returnDate !== newUpdateObject.returnDate
        || this.oldUpdateObject.returnTime !== newUpdateObject.returnTime
        || this.oldUpdateObject.outwardDate !== newUpdateObject.outwardDate
        || this.oldUpdateObject.outwardTime !== newUpdateObject.outwardTime
        || this.oldUpdateObject.passenger !== newUpdateObject.passenger
        || !isEqual(this.oldUpdateObject.origin, newUpdateObject.origin)
        || !isEqual(this.oldUpdateObject.destination, newUpdateObject.destination)
        || newUpdateObject.pricePerKm !== this.oldUpdateObject.pricePerKm
        || !isEqual(this.oldUpdateObject.waypoints, newUpdateObject.waypoints)
        || !isEqual(this.oldUpdateObject.schedules, newUpdateObject.schedules);
    },
    newUpdateObject () {
      return this.buildAdObject();
    },
    popupTitle () {
      if (this.isMajorUpdate && this.hasAsks) return this.$t('update.popup.major_update_asks.title');
      else if (this.isMajorUpdate && this.hasPotentialAds) return this.$t('update.popup.major_update_ads.title');
      else if (!this.isMajorUpdate && this.hasAsks) return this.$t('update.popup.minor_update_asks.title');
      return '';
    },
    popupContent () {
      if (this.isMajorUpdate && this.hasAsks) return this.$t('update.popup.major_update_asks.content');
      else if (this.isMajorUpdate && this.hasPotentialAds) return this.$t('update.popup.major_update_ads.content');
      else if (!this.isMajorUpdate && this.hasAsks) return this.$t('update.popup.minor_update_asks.content');
      return '';
    }
  },
  watch: {
    price() {
      //this.pricePerKm = (this.distance>0 ? Math.round(parseFloat(this.price) / this.distance * 100)/100 : this.pricePerKm);
      this.pricePerKm = (this.distance>0 ? parseFloat(this.price) / this.distance * 100/100 : this.pricePerKm);
      (this.pricePerKm>this.pricesRanges.forbidden) ? this.priceForbidden = true : this.priceForbidden = false;
    },
    distance() {
      let price = Math.round(this.distance * this.pricePerKm * 100)/100;
      this.roundPrice(price, this.regular ? 2 : 1);
    },
    route(){
      this.buildPointsToMap();
      if(this.route.direction !== null){this.buildDirectionWay();}
      this.$refs.mmapSummary.redrawMap();
      this.$refs.mmapRoute.redrawMap();
    },
    step(){
      this.$refs.mmapSummary.redrawMap();
      this.$refs.mmapRoute.redrawMap();
    },
    outwardTime(newValue,oldValue){
      if(newValue){
        this.outwardTime = (moment(newValue).isValid()) ? moment(this.ad.outwardTime).format("HH:mm") : newValue;
      }
    },
    returnTime(newValue,oldValue){
      if(newValue){
        this.returnTime = (moment(newValue).isValid()) ? moment(this.ad.returnTime).format("HH:mm") : newValue;
      }
    },
    ad: {
      immediate: true,
      handler () {
        const self = this;
        if(this.ad){
          this.origin = this.ad.origin;
          this.outwardDate = this.ad.outwardDate;
          this.outwardTime = moment(this.ad.outwardTime).utc().format();
          this.returnDate = this.ad.returnDate;
          this.returnTime = moment(this.ad.returnTime).isValid() ? moment(this.ad.returnTime).format() : null;
          this.initWaypoints = this.ad.outwardWaypoints.filter(point => {return point.address.id !== self.initOrigin.id && point.address.id !== self.initDestination.id;});
          this.initSchedule = isEmpty(this.ad.schedule) ? {} : this.ad.schedule;
          this.seats = this.ad.seatsDriver;
          this.luggage = this.ad.luggage;
          this.smoke = this.ad.smoke;
          this.bike = this.ad.bike;
          this.backSeats = this.ad.backSeats;
          this.music = this.ad.music;
          this.message = this.ad.message;
          this.price = parseFloat(this.ad.outwardDriverPrice);
          this.pricePerKm = parseFloat(this.ad.priceKm);
          this.role = this.ad.role;
          this.driver = this.ad.role === 1 || this.ad.role === 3;
          this.passenger = this.ad.role === 2 || this.ad.role === 3;
        }
      }
    }
  },
  methods: {
    buildPointsToMap: function(){
      this.pointsToMap.length = 0;
      // Set the origin point with custom icon
      if(this.origin !== null && this.origin !== undefined){
        let pointOrigin = this.buildPoint(this.origin.latitude,this.origin.longitude,this.origin.displayLabel,"/images/cartography/pictos/origin.png",[36, 42],[10, 25]);
        this.pointsToMap.push(pointOrigin);
      }
      // Set all the waypoints (default icon for now)
      this.route.waypoints.forEach((waypoint, index) => {
        if(waypoint.address != null){
          let currentWaypoint = this.buildPoint(waypoint.address.latitude,waypoint.address.longitude,waypoint.address.displayLabel);
          this.pointsToMap.push(currentWaypoint);
        }
      });
      // Set the destination point with custom icon
      if(this.destination !== null && this.destination !== undefined){
        let pointDestination = this.buildPoint(this.destination.latitude,this.destination.longitude,this.destination.displayLabel,"/images/cartography/pictos/destination.png",[36, 42],[10, 25]);
        this.pointsToMap.push(pointDestination);
      }
    },
    buildDirectionWay(){
      // You need to push the entire directPoints array because the MMap component can show multiple journeys
      this.directionWay.length = 0;
      let currentDirectionWay = {
        latLngs:this.route.direction.directPoints
      };
      this.directionWay.push(currentDirectionWay);
    },
    buildPoint: function(lat,lng,title="",pictoUrl="",size=[],anchor=[]){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {}
      };

      if(pictoUrl!==""){
        point.icon = {
          url:pictoUrl,
          size:size,
          anchor:anchor
        }
      }
        
      return point;      
    },
    buildUrl(route) {
      return `${this.baseUrl}/${route}`;
    },
    searchChanged: function(search) {
      this.passenger = search.passenger;
      this.driver = search.driver;
      this.origin = search.origin;
      this.destination = search.destination;
      this.regular = search.regular;
      this.outwardDate = search.date;
    },
    planificationChanged(planification) {
      this.outwardDate = planification.outwardDate;
      this.outwardTime = planification.outwardTime;
      this.returnDate = planification.returnDate;
      this.returnTime = planification.returnTime;
      this.schedules = planification.schedules;
      this.returnTrip = planification.returnTrip;
      this.fullschedule = planification.fullschedule;
      this.returnTimeIsValid = planification.returnTimeIsValid;
    },
    routeChanged(route) {
      if (this.isValidUpdate && this.initWaypointsCount && this.initWaypointsCount > 0) {
        this.initWaypointsCount--;
        if (this.initWaypointsCount === 0) {
          this.bodyIsFullyLoaded = true;
          this.oldUpdateObject = this.buildAdObject();
        }
      }
      this.route = route;
      this.origin = route.origin;
      this.destination = route.destination;
      this.distance = route.direction ? route.direction.distance : null;
      this.duration = route.direction ? route.direction.duration : null;
      this.selectedCommunities = route.communities ? route.communities : null;
    },
    postAd() {
      let postObject = this.buildAdObject();
      this.loading = true;
      maxios.post(this.buildUrl(this.$t('route.publish')),postObject,{
        headers:{
          'content-type': 'application/json'
        }
      })
        .then(response => {
          if (response.data) {
            if(response.data.result == undefined){
              this.snackErrorPublish.show = true;
              this.loading = false;
            }
            else{
              window.location.href = this.$t('route.myAds');
            }
          }
          //console.log(response);
        })
        .catch(function (error) {
          console.log(error);
        })
        .finally(() => {
          // this.loading = false;
        });
    },
    updateAd () {
      // double check
      if (!this.isValidUpdate) {
        this.snackbar = {
          message: this.$t('update.unavailable'),
          color: "error",
          show: true
        };
        return;
      }
      this.dialog = false;
      let postObject = this.buildAdObject();
      if (this.isMajorUpdate) {
        postObject.cancellationMessage = this.cancellationMessage;
      }
      this.loading = true;
      maxios.put(this.buildUrl(this.$t('route.update', {id: this.ad.id})),postObject,{
        headers:{
          'content-type': 'application/json'
        }
      })
        .then(response => {
          if (response.data && response.data.result.id) {
            this.snackbar = {
              message: this.isSearchToSave ? this.$t('searchToSave.success') : this.$t('update.success'),
              color: "success",
              show: true
            };
            window.location.href = this.$t('route.myAds');
          } else {
            this.snackbar = {
              message: this.isSearchToSave ? this.$t('searchToSave.error') : this.$t('update.error'),
              color: "error",
              show: true
            };
            this.loading = false;
          }
        })
        .catch(error => {
          console.log(error);
          this.snackbar = {
            message: this.isSearchToSave ? this.$t('searchToSave.error') : this.$t('update.error'),
            color: "error",
            show: true
          };
          this.loading = false;
        })
        .finally(() => {
          // this.loading = false;
        });
    },
    buildAdObject () {
      let postObject = {
        regular: this.regular,
        driver: this.driver,
        passenger: this.passenger,
        origin: this.origin,
        destination: this.destination,
        solidaryExclusive: this.solidaryExclusive,
        eventId : this.eventId,
      };
      if (this.isValidUpdate) postObject.id = this.ad.id;
      if (this.userDelegated) postObject.userDelegated = this.userDelegated;
      if (this.validWaypoints) postObject.waypoints = this.validWaypoints;
      if (this.selectedCommunities) postObject.communities = this.selectedCommunities;
      if (!this.regular) {
        if (this.outwardDate) postObject.outwardDate = this.outwardDate;
        if (this.outwardTime) postObject.outwardTime = this.outwardTime;
        if (this.returnDate) postObject.returnDate = this.returnDate;
        if (this.returnTime) postObject.returnTime = this.returnTime;
      } else if (this.schedules) {
        postObject.schedules = this.schedules;
      }
      // seats proposed as a driver (not handled yet for passengers)
      if (this.driver && this.seats) postObject.seatsDriver = this.seats;
      if (this.luggage != null) postObject.luggage = this.luggage;
      if (this.bike != null) postObject.bike = this.bike;
      if (this.backSeats != null) postObject.backSeats = this.backSeats;
      // price chosen by the driver (not handled yet for passengers)
      
      postObject.outwardDriverPrice = 0;
      if (this.driver && this.price) {
        // for now we just handle the outward price
        postObject.outwardDriverPrice = this.solidaryExclusive ? 0 : this.price;
      }
      
      postObject.priceKm = 0;
      if (this.pricePerKm){
        postObject.priceKm = this.solidaryExclusive ? 0 : this.pricePerKm;
      }

      if (this.message != null) postObject.message = this.message;
      // the following parameters are not used yet but we keep them here for possible future use
      if (this.regularLifetime) postObject.regularLifetime = this.regularLifetime;
      if (this.strictDate) postObject.strictDate = this.strictDate;
      if (this.strictRegular) postObject.strictRegular = this.strictRegular;
      if (this.strictPunctual) postObject.strictPunctual = this.strictPunctual;
      if (this.useTime) postObject.useTime = this.useTime;
      if (this.anyRouteAsPassenger) postObject.anyRouteAsPassenger = this.anyRouteAsPassenger;

      return postObject;
    },
    roundPrice (price, frequency, doneByUser = false) {
      if (price >= 0 && frequency > 0) {
        this.loadingPrice = true;
        maxios.post(this.$t('route.roundPrice'), {
          value: price,
          frequency: frequency
        }).then(resp => {
          if(this.price !== resp.data.value) {
            this.price = resp.data.value;
            if (doneByUser === true) {
              this.snackbar = {
                message: this.$t('messageRoundedPrice'),
                color: "success",
                show: true
              };
            }
          }
        }).catch(error => {
          // if an error occurred we set the original price
          this.price = price;
        }).finally(() => {
          this.loadingPrice = false;
          this.disableNextButton = false;
        })
      }
    },
    countWaypoints () {
      if (!isEmpty(this.initOrigin) && !isEmpty(this.initDestination)) {
        return 2;
      } else if (!isEmpty(this.initOrigin) || !isEmpty(this.initDestination)) {
        return 1;
      } else return 0;
    }
  }
};
</script>
<style scoped lang="scss">
#loading-screen {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  opacity: 0.8;
  z-index: 1000;
  background: lightgray;
}
</style>
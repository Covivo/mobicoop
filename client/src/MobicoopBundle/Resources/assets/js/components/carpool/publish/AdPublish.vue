<template>
  <v-container fluid>
    <!-- Title and subtitle -->
    <v-row 
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <h1>{{ $t('title') }}</h1>
        <h3 v-if="step==1">
          {{ $t('subtitle') }}
        </h3>
        <!-- todo : remove this awful trick !! -->
        <h3 v-else>
            &nbsp;
        </h3>
      </v-col>
    </v-row>
    <v-row
      v-if="solidaryAd"
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
      >
        <v-alert type="info">
          <p>{{ $t("messageSolidaryAd.message") }}</p>
        </v-alert>
      </v-col>
    </v-row>
    <v-row
      v-if="solidaryAd"
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        class="d-flex justify-center"
      >
        <v-switch
          v-model="solidary"
          color="success"
          inset
          :label="this.$t('messageSolidaryAd.switch.label')"
        />
      </v-col>
    </v-row>
    <v-row
      v-if="firstAd"
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
      >
        <v-alert type="info">
          <p>{{ $t("messageFirstAd.signUpDone", {'givenName':user.givenName}) }}.</p>
          <p>{{ $t("messageFirstAd.alert") }}</p>
        </v-alert>
      </v-col>
    </v-row>
    <!-- Stepper -->
    <v-row 
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <v-stepper
          v-model="step"
          alt-labels
          class="elevation-0"
        >
          <!-- Stepper Header -->
          <v-stepper-header
            v-show="step!==1"
            class="elevation-0"
          >
            <!-- Step 1 : search journey -->
            <v-stepper-step
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
              v-if="driver && !solidary"
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
              :step="driver ? 6 : 4"
              color="primary"
            >
              {{ $t('stepper.header.message') }}
            </v-stepper-step>
            <v-divider />

            <!-- Step 7 : summary -->
            <v-stepper-step
              color="primary"
              editable
              :step="driver ? 7 : 5"
            >
              {{ $t('stepper.header.summary') }}
            </v-stepper-step>
          </v-stepper-header>

          <!-- Stepper Content -->
          <v-stepper-items>
            <!-- Step 1 : search journey -->
            <v-stepper-content step="1">
              <search-journey
                :solidary-ad="solidary"
                display-roles
                :geo-search-url="geoSearchUrl"
                :user="user"
                :init-outward-date="outwardDate"
                :init-origin="origin"
                :init-destination="destination"
                :init-regular="regular"
                @change="searchChanged"
              />
            </v-stepper-content>

            <!-- Step 2 : planification -->
            <v-stepper-content step="2">
              <ad-planification
                :init-outward-date="outwardDate"
                :init-outward-time="outwardTime"
                :regular="regular"
                :default-margin-time="defaultMarginTime"
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
              v-if="driver && !solidary"
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
                v-if="pricePerKm >= 0.12"
                justify="center"
              >
                <v-col cols="8">
                  <v-card>
                    <v-card-text>
                      <p
                        v-if="pricePerKm >= 0.12 && pricePerKm < 0.3"
                        :class="colorPricePerKm + '--text'"
                      >
                        {{ $t('participation.mid') }}
                      </p>
                      <p
                        v-else-if="pricePerKm >= 0.3"
                        :class="colorPricePerKm + '--text'"
                      >
                        {{ $t('participation.high') }}
                      </p>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-stepper-content>

            <!-- Step 6 : message -->
            <v-stepper-content
              :step="driver ? 6 : 4"
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
              :step="driver ? 7 : 5"
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
                      :solidary="solidary"
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
              </v-container>
            </v-stepper-content>
          </v-stepper-items>
        </v-stepper>
      </v-col>
    </v-row>
    <!-- </v-stepper-content> -->

    <!-- Buttons Previous and Next step -->
    <v-layout
      mt-5
      justify-center
    >
      <v-btn
        v-if="step > 1"
        rounded
        outlined
        color="primary" 
        align-center
        @click="--step"
      >
        {{ $t('stepper.buttons.previous') }}
      </v-btn>

      <v-btn
        v-if="validNext"
        :disabled="!validNext"
        rounded
        color="primary"
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
              @click="postAd"
            >
              {{ $t('stepper.buttons.publish_ad') }}
            </v-btn>
          </div>
        </template>
        <span>{{ $t('stepper.buttons.notValid') }}</span>
      </v-tooltip>
    </v-layout>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/carpool/publish/AdPublish.json";
import TranslationsClient from "@clientTranslations/components/carpool/publish/AdPublish.json";

import axios from "axios";
import SearchJourney from "@components/carpool/search/SearchJourney";
import AdPlanification from "@components/carpool/publish/AdPlanification";
import AdRoute from "@components/carpool/publish/AdRoute";
import AdSummary from "@components/carpool/publish/AdSummary";
import MMap from '@components/utilities/MMap'
import L from "leaflet";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
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
    resultsUrl: {
      type: String,
      default: 'covoiturage/annonce/{id}/resultats'
    },
    defaultMarginTime: {
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
    solidaryAd: {
      type: Boolean,
      default: false
    },
   

  },
  data() {
    return {
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
      pricePerKm: this.defaultPriceKm,
      message: null,
      baseUrl: window.location.origin,
      loading: false,
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
      solidary: this.solidaryAd,
      numberSeats : [ 1,2,3,4],
      seats : 3
    }


  },
  computed: {
   
    hintPricePerKm() {
      return this.pricePerKm+'€/km';
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
      if ((this.driver && this.step != 7) || (!this.driver && this.step != 5)) return false;
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
      if(this.step==2 && !this.regular && !(this.outwardDate && this.outwardTime)) return false;
      // Step 2 punctual, round-trip chosen : you have to set the outward date & time
      if(this.step==2 && !this.regular && this.returnTrip && !(this.returnDate && this.returnTime)) return false;


      return true;
    },
    urlToCall() {
      return `${this.baseUrl}/${this.$t('route.publish')}`;
    },
    pointEscapedPrice(){
      return this.price.replace(".",",");
    },
    colorPricePerKm(){
      if (this.pricePerKm < 0.12) {
        return "success";
      } else if (this.pricePerKm >= 0.12 && this.pricePerKm < 0.3) {
        return "warning";
      } else {
        return "error";
      }
    }
  },
  watch: {
    price() {
      this.pricePerKm = (this.distance>0 ? Math.round(parseFloat(this.price) / this.distance * 100)/100 : this.defaultPriceKm);
    },
    distance() {
      this.price = Math.round(this.distance * this.pricePerKm * 100)/100;
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
        if(waypoint.address !== null){
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
      }        
      this.directionWay.push(currentDirectionWay);
    },
    buildPoint: function(lat,lng,title="",pictoUrl="",size=[],anchor=[]){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {}
      }

      if(pictoUrl!==""){
        point.icon = {
          url:pictoUrl,
          size:size,
          anchor:anchor
        }
      }
        
      return point;      
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
    },
    routeChanged(route) {
      this.route = route;
      this.origin = route.origin;
      this.destination = route.destination;
      this.distance = route.direction ? route.direction.distance / 1000 : null;
      this.duration = route.direction ? route.direction.duration : null;
      this.selectedCommunities = route.communities ? route.communities : null;
    },
    postAd() {
      let postObject = {
        regular: this.regular,
        driver: this.driver,
        passenger: this.passenger,
        origin: this.origin,
        destination: this.destination,
        solidary: this.solidary
      };
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
      if (this.seats) postObject.seats = this.seats;
      if (this.luggage) postObject.luggage = this.luggage;
      if (this.bike) postObject.bike = this.bike;
      if (this.backSeats) postObject.backSeats = this.backSeats;
      if (this.price) postObject.price = this.solidary ? 0 : this.price;
      if (this.pricePerKm) postObject.priceKm = this.solidary ? 0 : this.pricePerKm;
      if (this.message) postObject.message = this.message;
      // the following parameters are not used yet but we keep them here for possible future use
      if (this.regularLifetime) postObject.regularLifetime = this.regularLifetime;
      if (this.strictDate) postObject.strictDate = this.strictDate;
      if (this.strictRegular) postObject.strictRegular = this.strictRegular;
      if (this.strictPunctual) postObject.strictPunctual = this.strictPunctual;
      if (this.useTime) postObject.useTime = this.useTime;
      if (this.anyRouteAsPassenger) postObject.anyRouteAsPassenger = this.anyRouteAsPassenger;

      this.loading = true;
      var self = this;
      axios.post(this.urlToCall,postObject,{
        headers:{
          'content-type': 'application/json'
        }
      })
        .then(function (response) {
          if (response.data && response.data.result && response.data.result.id) {
            // uncomment when results page activated
            // var urlRedirect = `${self.baseUrl}/`+self.resultsUrl.replace(/{id}/,response.data.result.id);
            // window.location.href = urlRedirect;
            window.location.href = "/";
          }
          //console.log(response);
        })
        .catch(function (error) {
          console.log(error);
        })
        .finally(function () {
          self.loading = false;
        });
    }

  }
};
</script>
<style scoped lang="scss">
</style>
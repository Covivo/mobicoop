<template>
  <v-content>
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
                color="success"
              >
                {{ $t('stepper.header.search_journey') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 2 : planification -->
              <v-stepper-step
                editable
                :step="2"
                color="success"
              >
                {{ $t('stepper.header.planification') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 3 : map -->
              <v-stepper-step
                editable
                :step="3"
                color="success"
              >
                {{ $t('stepper.header.map') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 4 : passengers (if driver) -->
              <v-stepper-step
                v-if="driver"
                editable
                :step="4"
                color="success"
              >
                {{ $t('stepper.header.passengers') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 5 : participation (if driver) -->
              <v-stepper-step
                v-if="driver"
                editable
                :step="5"
                color="success"
              >
                {{ $t('stepper.header.participation') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 6 : message -->
              <v-stepper-step
                editable
                :step="driver ? 6 : 4"
                color="success"
              >
                {{ $t('stepper.header.message') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 7 : summary -->
              <v-stepper-step
                color="success"
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
                  display-roles
                  :geo-search-url="geoSearchUrl"
                  :user="user"
                  :init-outward-date="outwardDate"
                  :init-origin="origin"
                  :init-destination="destination"
                  :regular="true"
                  @change="searchChanged"
                />
              </v-stepper-content>

              <!-- Step 2 : planification -->
              <v-stepper-content step="2">
                <ad-planification 
                  :init-outward-date="outwardDate"
                  :regular="regular"
                  :default-margin-time="defaultMarginTime" 
                  @change="planificationChanged"
                />
              </v-stepper-content>

              <!-- Step 3 : route -->
              <v-stepper-content step="3">
                <ad-route 
                  :geo-search-url="geoSearchUrl"
                  :geo-route-url="geoRouteUrl"
                  :user="user"
                  :init-origin="origin"
                  :init-destination="destination"
                  :communities="communities"
                  @change="routeChanged"
                />
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
                      :items="[1,2,3,4]"
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
                      color="success"
                    />
                  </v-col>
                  <v-col
                    cols="1"
                    align="left"
                  >
                    <v-tooltip right>
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
                      color="success"
                    />
                  </v-col>
                  <v-col
                    cols="1"
                    align="left"
                  >
                    <v-tooltip right>
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
                      color="success"
                    />
                  </v-col>
                  <v-col
                    cols="1"
                    align="left"
                  >
                    <v-tooltip right>
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
                v-if="driver"
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
                    />
                  </v-col>
                  
                  <v-col
                    cols="2"
                    align="left"
                  >
                    {{ $t('stepper.content.participation.passengers') }}
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
                        :price="price"
                        :route="route"
                        :message="message"
                        :user="user"
                      />
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col cols="12">
                      <m-map
                        ref="mmap"
                        type-map="adSummary"
                        :points="pointsToMap"
                        :ways="directionWay"
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
          color="success" 
          align-center
          @click="--step"
        >
          {{ $t('stepper.buttons.previous') }}
        </v-btn>

        <v-btn
          v-if="((driver && step < 7) || (step<5)) && origin != null && destination != null && (passenger || driver) && (regular || outwardDate)"
          rounded
          color="success"
          align-center
          style="margin-left: 30px;"
          @click="step++"
        >
          {{ $t('stepper.buttons.next') }}
        </v-btn>
        <v-btn
          v-if="valid"
          :loading="loading"
          :disabled="loading"
          rounded
          color="success"
          style="margin-left: 30px;"
          align-center
          @click="postAd"
        >
          {{ $t('stepper.buttons.publish_ad') }}
        </v-btn>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/AdPublish.json";
import TranslationsClient from "@clientTranslations/components/carpool/AdPublish.json";

import axios from "axios";
import moment from 'moment'
import SearchJourney from "@components/carpool/SearchJourney";
import AdPlanification from "@components/carpool/AdPlanification";
import AdRoute from "@components/carpool/AdRoute";
import AdSummary from "@components/carpool/AdSummary";
import MMap from '@components/base/MMap'
import L from "leaflet";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
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
    communities: {
      type: Array,
      default: null
    },
    defaultPriceKm: {
      type: Number,
      default: 0.06
    },
    publishUrl: {
      type: String,
      default: 'covoiturage/annonce/poster'
    },
    resultsUrl: {
      type: String,
      default: 'covoiturage/annonce/{id}/resultats'
    },
    defaultMarginTime: {
      type: Number,
      default: null
    },
  },
  data() {
    return {
      distance: 0, 
      duration: 0,
      outwardDate: null,
      outwardTime: null,
      returnDate: null,
      returnTime: null,
      origin: null,
      destination: null,
      regular: true,
      step:1,
      driver: true,
      passenger: true,
      seats: 1,
      luggage: false,
      bike: false,
      backSeats: false,
      schedules: null,
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
      bbox:null
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
      // step validation
      if ((this.driver && this.step != 7) || (!this.driver && this.step != 5)) return false;
      // role validation
      if (this.driver === false && this.passenger === false) return false;
      // route validation
      if (this.distance<=0 || this.duration<=0 || !this.origin || !this.destination || !this.route) return false;
      // punctual date validation
      if (!this.regular && !(this.outwardDate && this.outwardTime)) return false;
      // regular date validation
      if (this.regular && !this.schedules) return false;
      // validation ok
      return true;
    },
    urlToCall() {
      return `${this.baseUrl}/${this.publishUrl}`;
    },
  },
  watch: {
    price() {
      this.pricePerKm = (this.distance>0 ? Math.round(this.price / this.distance * 100)/100 : this.defaultPriceKm);
    },
    distance() {
      this.price = Math.round(this.distance * this.pricePerKm * 100)/100;
    },
    route(){
      this.buildPointsToMap();
      if(this.route.direction !== null){this.buildDirectionWay();}
    },
    step(){
      this.$refs.mmap.redrawMap();
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
      this.directionWay.push(this.route.direction.directPoints);
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
        destination: this.destination
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
      if (this.price) postObject.price = this.price;
      if (this.message) postObject.message = this.message;
      this.loading = true;
      var self = this;
      axios.post(this.urlToCall,postObject,{
        headers:{
          'content-type': 'application/json'
        }
      })
        .then(function (response) {
          if (response.data && response.data.result && response.data.result.id) {
            var urlRedirect = `${self.baseUrl}/`+self.resultsUrl.replace(/{id}/,response.data.result.id);
            window.location.href = urlRedirect;
          }
          console.log(response);
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
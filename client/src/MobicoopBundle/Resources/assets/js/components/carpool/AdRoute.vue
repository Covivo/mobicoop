<template>
  <v-container
    fluid
  >
    <!-- Origin -->
    <v-row
      align="center"
      no-gutters
    >
      <v-col
        cols="6"
        offset="3"
      >
        <GeoComplete
          name="origin"
          :label="$t('origin.label')"
          :url="geoSearchUrl"
          :init-address="initOriginAddress"
          :required-error="$t('origin.error')"
          required
          @address-selected="originSelected"
        />
      </v-col>
    </v-row>

    <v-row
      v-show="!waypoints[3].visible"
      align="center"
      no-gutters
    >
      <v-col
        cols="6"
        offset="3"
        align="left"
      >
        <v-btn
          text
          icon
          @click="addWaypoint"
        >
          <v-icon>
            mdi-plus-circle-outline
          </v-icon>
        </v-btn>
        {{ $t('addWaypoint') }}
      </v-col>
    </v-row>

    <!-- Waypoints -->
    <!-- For now additional waypoints are hardcorded and limited to 4 -->
    <v-row
      v-show="waypoints[0].visible"
      align="center"
      no-gutters
    >
      <v-col
        cols="6"
        offset="3"
      >
        <GeoComplete
          name="etape1"
          :label="$t('waypoint1.label')"
          :url="geoSearchUrl"
          @address-selected="waypointSelected(0, ...arguments)"
        />
      </v-col>

      <v-col
        v-show="!waypoints[1].visible"
        cols="1"
      >
        <v-btn
          text
          icon
          @click="removeWaypoint(0)"
        >
          <v-icon>
            mdi-close-circle-outline
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <v-row 
      v-show="waypoints[1].visible"
      align="center"
      no-gutters
    >
      <v-col
        cols="6"
        offset="3"
      >
        <GeoComplete
          name="etape2"
          :label="$t('waypoint2.label')"
          :url="geoSearchUrl"
          @address-selected="waypointSelected(1, ...arguments)"
        />
      </v-col>

      <v-col
        v-show="!waypoints[2].visible"
        cols="1"
      >
        <v-btn
          text
          icon
          @click="removeWaypoint(1)"
        >
          <v-icon>
            mdi-close-circle-outline
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <v-row
      v-show="waypoints[2].visible"
      align="center"
      no-gutters
    >
      <v-col
        cols="6"
        offset="3"
      >
        <GeoComplete
          name="etape3"
          :label="$t('waypoint3.label')"
          :url="geoSearchUrl"
          @address-selected="waypointSelected(2, ...arguments)"
        />
      </v-col>

      <v-col
        v-show="!waypoints[3].visible"
        cols="1"
      >
        <v-btn
          text
          icon
          @click="removeWaypoint(2)"
        >
          <v-icon>
            mdi-close-circle-outline
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <v-row
      v-show="waypoints[3].visible"
      align="center"
      no-gutters
    >
      <v-col
        cols="6"
        offset="3"
      >
        <GeoComplete
          name="etape4"
          :label="$t('waypoint4.label')"
          :url="geoSearchUrl"
          @address-selected="waypointSelected(3, ...arguments)"
        />
      </v-col>

      <v-col
        cols="1"
      >
        <v-btn
          text
          icon
          @click="removeWaypoint(3)"
        >
          <v-icon>
            mdi-close-circle-outline
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <!-- destination -->
    <v-row
      align="center"
      no-gutters
    >
      <v-col
        cols="6"
        offset="3"
      >
        <GeoComplete
          name="destination"
          :label="$t('destination.label')"
          :required-error="$t('destination.error')"
          required
          :url="geoSearchUrl"
          :init-address="initDestinationAddress"
          @address-selected="destinationSelected"
        />
      </v-col>
    </v-row>

    <!-- Avoid motorway -->
    <v-row
      align="center"
      justify="center"
      dense
    >
      <v-col
        cols="6"
      >
        <v-checkbox
          v-model="avoidMotorway"
          class="mt-0"
          :label="$t('avoidMotorway')"
          color="success"
          hide-details
          @change="emitEvent"
        />
      </v-col>
    </v-row>

    <!-- Communities -->

    <!-- Map (soon...) -->
    <v-row
      v-if="direction"
      align="center"
      justify="center"
    >
      <v-col
        cols="6"
      >
        <!-- Route detail -->
        <v-card>
          <v-row
            align="center"
            justify="space-around"
          >
            {{ $t('distance') }} : {{ direction.distance / 1000 }} km
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import axios from "axios";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/AdRoute.json";
import TranslationsClient from "@clientTranslations/components/carpool/AdRoute.json";

import GeoComplete from "@components/utilities/GeoComplete";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    GeoComplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    geoRouteUrl: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
      default: null
    },
    initOriginAddress: {
      type: Object,
      default: null
    },
    initDestinationAddress: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      origin: null,
      destination: null,
      waypoints: [
        {
          visible: false,
          address: null
        },
        {
          visible: false,
          address: null
        },
        {
          visible: false,
          address: null
        },
        {
          visible: false,
          address: null
        },
      ],
      avoidMotorway: false,
      direction: null
    };
  },
  watch: {
    initOriginAddress() {
      this.origin = this.initOriginAddress;
      this.getRoute();
    },
    initDestinationAddress() {
      this.destination = this.initDestinationAddress;
      this.getRoute();
    }
  },
  methods: {
    originSelected: function(address) {
      this.origin = address;
      this.getRoute();
      //this.emitEvent();
    },
    destinationSelected: function(address) {
      this.destination = address;
      this.getRoute();
      //this.emitEvent();
    },
    waypointSelected(id,address) {
      this.waypoints[id].address = address;
      this.getRoute();
      //this.emitEvent();
    },
    getRoute() {
      if (this.origin != null && this.destination != null) {
        let params = `?points[0][longitude]=${this.origin.longitude}&points[0][latitude]=${this.origin.latitude}`;
        let nbWaypoints = 0;
        this.waypoints.forEach((item,key) => {
          if (item.visible) {
            nbWaypoints++;
            params += `&points[${nbWaypoints}][longitude]=${item.address.longitude}&points[${nbWaypoints}][latitude]=${item.address.latitude}`;
          }
        });
        nbWaypoints++;
        params += `&points[${nbWaypoints}][longitude]=${this.destination.longitude}&points[${nbWaypoints}][latitude]=${this.destination.latitude}`;
        axios
          .get(`${this.geoRouteUrl}${params}`)
          .then(res => {
            this.direction = res.data.member[0];
            this.emitEvent();
          })
          .catch(err => {
            console.error(err);
            this.emitEvent();
          });
      } else {
        this.emitEvent();
      }
    },
    emitEvent: function() {
      this.$emit("change", {
        origin: this.origin,
        destination: this.destination,
        waypoints: this.waypoints,
        avoidMotorway: this.avoidMotorway,
        direction: this.direction
      });
    },
    addWaypoint() {
      if (!this.waypoints[0].visible) {
        this.waypoints[0].visible = true;
      } else if (this.waypoints[0].visible && !this.waypoints[1].visible) {
        this.waypoints[1].visible = true;
      } else if (this.waypoints[0].visible && this.waypoints[1].visible && !this.waypoints[2].visible) {
        this.waypoints[2].visible = true;
      } else if (this.waypoints[0].visible && this.waypoints[1].visible && this.waypoints[2].visible && !this.waypoints[3].visible) {
        this.waypoints[3].visible = true;
      }
    },
    removeWaypoint(id) {
      this.waypoints[id].visible = false;
      this.waypoints[id].address = null;
      this.getRoute();
    }
  }
};
</script>
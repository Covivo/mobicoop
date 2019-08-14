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
          <v-icon
            large
          >
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
          <v-icon
            large
          >
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
          <v-icon
            large
          >
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
          <v-icon
            large
          >
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
          <v-icon
            large
          >
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
  </v-container>
</template>

<script>
import { merge } from "lodash";
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
      ]
    };
  },
  watch: {
    initOriginAddress() {
      this.origin = this.initOriginAddress;
    },
    initDestinationAddress() {
      this.destination = this.initDestinationAddress;
    }
  },
  methods: {
    originSelected: function(address) {
      this.origin = address;
      this.emitEvent();
    },
    destinationSelected: function(address) {
      this.destination = address;
      this.emitEvent();
    },
    waypointSelected(id,address) {
      this.waypoints[id].address = address;
      this.emitEvent();
    },
    emitEvent: function() {
      this.$emit("change", {
        origin: this.origin,
        destination: this.destination,
        waypoints: this.waypoints
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
    }
  }
};
</script>
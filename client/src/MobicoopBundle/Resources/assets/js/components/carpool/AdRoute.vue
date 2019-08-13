<template>
  <v-container
    grid-list-md
    text-xs-center
  >
    <v-layout
      align-center
      justify-center
    >
      <v-flex
        xs7
      >
        <v-layout
          mt-5
        >
          <GeoComplete
            name="origin"
            :label="$t('origin.label')"
            :url="geoSearchUrl"
            @address-selected="originSelected"
          />
        </v-layout>
        
        <p v-if="!waypoint4">
          <v-btn
            text
            icon
            @click="addWaypoint"
          >
            <v-icon
              large
            >
              mdi-chevron-right
            </v-icon>
          </v-btn>
          {{ $t('addWaypoint') }}
        </p>

        <template v-if="waypoint1">
          <v-layout
            mt-10
          >
            <GeoComplete
              name="etape1"
              label="etape1"
              :url="geoSearchUrl"
              @address-selected="destinationSelected"
            />

            <p v-if="!waypoint2">
              <v-btn
                text
                icon
                @click="removeWaypoint"
              >
                <v-icon
                  large
                >
                  mdi-delete
                </v-icon>
              </v-btn>
            </p>
          </v-layout>
        </template>

        <template v-if="waypoint2">
          <v-layout
            mt-10
          >
            <GeoComplete
              name="etape2"
              label="etape2"
              :url="geoSearchUrl"
              @address-selected="destinationSelected"
            />
            <p v-if="!waypoint3">
              <v-btn
                text
                icon
                @click="removeWaypoint"
              >
                <v-icon
                  large
                >
                  mdi-delete
                </v-icon>
              </v-btn>
            </p>
          </v-layout>
        </template>

        <template v-if="waypoint3">
          <v-layout
            mt-10
          >
            <GeoComplete
              name="etape3"
              label="etape3"
              :url="geoSearchUrl"
              @address-selected="destinationSelected"
            />
            <p v-if="!waypoint4">
              <v-btn
                text
                icon
                @click="removeWaypoint"
              >
                <v-icon
                  large
                >
                  mdi-delete
                </v-icon>
              </v-btn>
            </p>
          </v-layout>
        </template>

        <template v-if="waypoint4">
          <v-layout
            mt-10
          >
            <GeoComplete
              name="etape4"
              label="etape4"
              :url="geoSearchUrl"
              @address-selected="destinationSelected"
            />
            <p>
              <v-btn
                text
                icon
                @click="removeWaypoint"
              >
                <v-icon
                  large
                >
                  mdi-delete
                </v-icon>
              </v-btn>
            </p>
          </v-layout>
        </template>

        <v-layout
          mt-10
        >
          <GeoComplete
            name="destination"
            :label="$t('destination.label')"
            :url="geoSearchUrl"
            @address-selected="destinationSelected"
          />
        </v-layout>
      </v-flex>
    </v-layout>
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
    }
  },
  data() {
    return {
      origin: null,
      destination: null,
      waypoint1: false,
      waypoint2: false,
      waypoint3: false,
      waypoint4: false
    };
  },
  computed: {
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
    emitEvent: function() {
      this.$emit("change", {
        origin: this.origin,
        destination: this.destination
      });
    },
    addWaypoint() {
      if (this.waypoint1 === false) {
        this.waypoint1 = true;
        return;
      }
      if (this.waypoint2 === false) {
        this.waypoint2 = true;
        return;
      }
      if (this.waypoint3 === false) {
        this.waypoint3 = true;
        return;
      }
      if (this.waypoint4 === false) {
        this.waypoint4 = true;
      }
      return;
    },
    removeWaypoint() {
      if (this.waypoint4 === true) {
        this.waypoint4 = false;
        return;
      }
      if (this.waypoint3 === true) {
        this.waypoint3 = false;
        return;
      }
      if (this.waypoint2 === true) {
        this.waypoint2 = false;
        return;
      }
      if (this.waypoint1 === true) {
        this.waypoint1 = false;
      }
      return;
    }
  }
};
</script>
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
            :label="$t('stepper.content.map.origin.label')"
            :placeholder="$t('stepper.content.map.origin.placeholder')"
            :url="geoSearchUrl"
            mt-10
            @address-selected="originSelected"
          />
        </v-layout>
        <p>
          <v-icon
            large
          >
            mdi-chevron-right
          </v-icon>
          {{ $t('stepper.content.map.ad_waypoint') }}
        </p>
        <v-layout
          mt-10
        >
          <GeoComplete
            :label="$t('stepper.content.map.destination.label')"
            :placeholder="$t('stepper.content.map.destination.placeholder')"
            :url="geoSearchUrl"
            name="destination"
            mt-15
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
      destination: null
    };
  },
  computed: {
  },
  methods: {
    originSelected: function(address) {
      this.origin = address;
      this.$emit("origin-selected", address);
    },
    destinationSelected: function(address) {
      this.destination = address;
      this.$emit("destination-selected", address);
    },
  }
};
</script>
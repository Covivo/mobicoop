<template>
  <v-content>
    <v-layout
      align-center
      justify-center
    >
      <v-flex xs8>
        <h1>{{ $t('title') }}</h1>
        <h3 v-html="$t('subtitle')" />
        <GeoComplete :url="geoSearchUrl" />
      </v-flex>
    </v-layout>
  </v-content>
</template>

<script>
import Translations from "../../../translations/components/HomeSearch.json";
import GeoComplete from "./GeoComplete";

export default {
  i18n: {
    messages: Translations
  },
  components: {
    GeoComplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    route: {
      type: String,
      default: ""
    }
  }
}
</script>
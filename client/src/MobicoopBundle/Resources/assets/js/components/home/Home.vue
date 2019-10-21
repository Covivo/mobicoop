<template>
  <v-content color="secondary">
    <v-container
      grid-list-md
      text-xs-center
    >
      <!-- Title and subtitle -->
      <v-row
        align="center"
        class="mt-5"
        justify="center"
      >
        <v-col
          cols="6"
        >
          <h1>{{ $t('title') }}</h1>
          <h3 v-html="$t('subtitle')" />
        </v-col>
      </v-row>
      <search
        :geo-search-url="geoSearchUrl" 
        :user="user"
        :regular="regular"
        :punctual-date-optional="punctualDateOptional"
      />
    </v-container>
  </v-content>
</template>

<script>
import {merge} from "lodash";
import Translations from "@translations/components/home/Home.json";
import TranslationsClient from "@clientTranslations/components/home/Home.json";
import Search from "@components/carpool/search/Search";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    Search
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
    regular: {
      type: Boolean,
      default: false
    }, 
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    // params to add to the publish and search routes
    params: {
      type: Object,
      default: null
    }
  }
};
</script>
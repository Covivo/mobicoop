<template>
  <v-content color="secondary">
    <v-container
      text-xs-center
      grid-list-md
    >
      <!-- Title and subtitle -->
      <v-layout
        row
        justify-center
        align-center
        class="mt-5"
      >
        <v-flex xs6>
          <h1>{{ $t('title') }}</h1>
          <h3 v-html="$t('subtitle')" />
        </v-flex>
      </v-layout>

      <v-layout
        row
        justify-center
      >
        <v-flex
          xs6
        >
          <!--SearchJourney-->
          <search-journey
            :geo-search-url="geoSearchUrl"
            :user="user"
            @change="searchChanged"
          />
        </v-flex>
      </v-layout>
      
      <!-- Buttons -->
      <v-layout
        class="mt-5"
        justify-center
        row
      >
        <v-flex xs10>
          <v-flex
            xs4
          >
            <v-btn
              rounded
              outlined
              disabled
              @click="publish"
            >
              {{ $t('buttons.shareAnAd.label') }}
            </v-btn>
          </v-flex>
          <v-flex xs4>
            <v-btn
              color="success"
              rounded
              :disabled="searchUnavailable"
              @click="search"
            >
              {{ $t('buttons.search.label') }}
            </v-btn>
          </v-flex>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
import SearchJourney from "./SearchJourney";

import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/HomeSearch.json";
import TranslationsClient from "@clientTranslations/components/HomeSearch.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    SearchJourney
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    route: {
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
      regular: false,
      date: null,
      origin: null,
      destination: null,
      baseUrl: window.location.origin
    };
  },
  computed: {
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/origine/destination/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.date}/resultats`;
    },
    searchUnavailable() {
      return !this.origin || !this.destination;
    }
  },
  methods: {
    searchChanged: function(search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.regular = search.regular;
      this.date = search.date;
    },
    search: function() {
      window.location.href = this.urlToCall;
    },
    publish: function() {
      console.error("publish !");
    }
  }
};
</script>
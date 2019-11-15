<template>
  <v-content color="secondary">
    <v-container
      text-center
    >
      <!-- Title and subtitle -->
      <v-row
        align="center"
        class="mt-5"
        justify="center"
      >
        <v-col
          cols="12"
          xl="6"
          lg="9"
          md="12"
          class="text-center"
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
      <v-row
        align="center"
        class="mt-5"
        justify="center"
      >
        <v-col
          cols="12"
          xl="6"
          lg="9"
          md="12"
        >
          <home-content 
            :community-display="communityDisplay"
            :event-display="eventDisplay"
            :solidary-display="solidaryDisplay"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import {merge} from "lodash";
import Translations from "@translations/components/home/Home.json";
import TranslationsClient from "@clientTranslations/components/home/Home.json";
import Search from "@components/carpool/search/Search";
import HomeContent from "@components/home/HomeContent";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    Search,
    HomeContent
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
    solidaryDisplay: {
      type: Boolean,
      default: false
    },
    eventDisplay: {
      type: Boolean,
      default: false
    },
    communityDisplay: {
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
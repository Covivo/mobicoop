<template>
  <v-content>
    <v-container fluid>
      <v-row 
        justify="center"
      >
        <v-col
          cols="8"
          md="8"
          xl="6"
          align="center"
        >    
          <!-- Matching header -->
          <matching-header 
            :origin="origin"
            :destination="destination"
            :date="date"
            :regular="regular"
          />

          <!-- Matching filter -->
          <matching-filter />

          <!-- Matching results -->
          <matching-results
            :origin-latitude="originLatitude"
            :origin-longitude="originLongitude"
            :destination-latitude="destinationLatitude"
            :destination-longitude="destinationLongitude"
            :date="date"
            :url="url"
            :regular="regular"
            :show-regular="showRegular"
            :user="user"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/Matching.json";
import TranslationsClient from "@clientTranslations/components/carpool/Matching.json";
import MatchingHeader from "./MatchingHeader";
import MatchingFilter from "./MatchingFilter";
import MatchingResults from "./MatchingResults";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingHeader,
    MatchingFilter,
    MatchingResults
  },
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
    origin: {
      type: String,
      default: null
    },
    destination: {
      type: String,
      default: null
    },
    originLatitude: {
      type: String,
      default: null
    },
    originLongitude: {
      type: String,
      default: null
    },
    destinationLatitude: {
      type: String,
      default: null
    },
    destinationLongitude: {
      type: String,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    url: {
      type: String,
      default: null
    },
    user: {
      type:Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    showRegular: {
      type: Boolean,
      default: false
    }
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
    };
  },
  methods :{
    // TODO : REMOVE WHEN START CODING FILTER COMPONENT
    remove (item) {
      this.chips.splice(this.chips.indexOf(item), 1)
      this.chips = [...this.chips]
    },
  }
};
</script>
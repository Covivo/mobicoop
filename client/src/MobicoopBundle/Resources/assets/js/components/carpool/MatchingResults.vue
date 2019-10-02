<template>
  <v-content>
    <!--loading overlay-->
    <v-overlay 
      :value="loading"
      absolute
    >
      <v-progress-circular
        indeterminate
      />
    </v-overlay>

    <v-container fluid>
      <!-- Number of matchings -->
      <v-row 
        justify="center"
        align="center"
      >
        <v-col
          cols="12"
          align="left"
        >
          {{ $tc('matchingNumber', numberOfMatchings, { number: numberOfMatchings }) }}
        </v-col>
      </v-row>

      <!-- Matching results -->
      <v-row 
        v-for="(matching,index) in matchings"
        :key="index"
        justify="center"
      >
        <v-col
          cols="12"
          align="left"
        >    
          <!-- Matching result -->
          <matching-result
            :matching="matching"
            :user="user"
            :regular="regular"
            @carpool="carpool"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import axios from "axios";
import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/MatchingResults.json";
import TranslationsClient from "@clientTranslations/components/carpool/MatchingResults.json";
import MatchingResult from "./MatchingResult";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingResult
  },
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
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
      loading : true,
      matchings: null
    };
  },
  computed: {
    isoDate() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).toISOString()
        : "";
    },
    numberOfMatchings() {
      return this.matchings ? Object.keys(this.matchings).length : 0 // ES5+
    }
  },
  created() {
    this.loading = true;
    axios.get(this.url, {
      params: {
        "origin_latitude": Number(this.originLatitude),
        "origin_longitude": Number(this.originLongitude),
        "destination_latitude": Number(this.destinationLatitude),
        "destination_longitude": Number(this.destinationLongitude),
        "date": this.isoDate,
        "regular": this.regular 
      }
    })
      .then((response) => {
        this.loading = false;
        this.matchings = response.data;
      })
      .catch((error) => {
        console.log(error);
      });
  },
  methods: {
    carpool(params) {
      this.$emit("carpool", {
        proposal: params.proposal,
        driver: params.driver,
        passenger: params.passenger
      });
    }
  }
};
</script>
<style>
</style>
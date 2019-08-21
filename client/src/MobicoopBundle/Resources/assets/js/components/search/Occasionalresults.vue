<template>
  <v-content>
    <v-container
      fluid
    />
    <h3>{{ Results }}</h3>
  </v-content>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/simpleResults.json";
import TranslationsClient from "@clientTranslations/components/simpleResults.json";
import moment from "moment";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
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
    matchingSearchUrl: {
      type: String,
      default: null
    },
  },
  data : function() {
    return {
      Results : null,
      loading : true
    };
  },
  created() {
    //fill Results
    axios.get("/matching/search", {
      params: {
        "origin_latitude": Number(this.originLatitude),
        "origin_longitude": Number(this.originLongitude),
        "destination_latitude": Number(this.destinationLatitude),
        "destination_longitude": Number(this.destinationLongitude),
        "date": this.dateFormated(this.date)
      }
    })
      .then((response) => {
        console.log(response);
        this.loading = false;
        return this.Results = response.data[0];
      })
      .catch((error) => {
        console.log(error);
      });
  },
  methods :{
    dateFormated(date) {
      return moment(new Date(date)).utcOffset("+00:00").format()
    },
    //fill Results
  }
};
</script>

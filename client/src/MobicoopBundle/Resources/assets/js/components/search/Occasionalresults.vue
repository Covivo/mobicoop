<template>
  <v-content>
    <v-container
      fluid
    >
      <!--      loading overlay-->
      <!--      <v-overlay :value="loading">-->
      <!--        <v-progress-circular-->
      <!--          indeterminate-->
      <!--          size="64"-->
      <!--        />-->
      <!--      </v-overlay>-->
      <v-row
        justify="center"
        align="center"
        no-gutters
      >
        <v-col
          cols="2"
        >
          {{ origin }}
        </v-col>
        <v-col
          cols="1"
          justify="center"
          align="center"
        >
          <v-icon
            :color="'yellow darken-2'"
            size="64"
            class="no-line-height"
          >
            mdi-ray-start-end
          </v-icon>
        </v-col>
        <v-col
          cols="2"
        >
          {{ destination }}
        </v-col>
        <v-col
          cols="1"
          justify="center"
          align="center"
        >
          <v-btn
            color="primary"
            fab
            small
            depressed
          >
            <v-icon>
              mdi-lead-pencil
            </v-icon>
          </v-btn>
        </v-col>
      </v-row>
      <!-- date row-->
      <v-row
        justify="center"
        align="center"
        no-gutters
      >
        <v-col
          cols="6"
        >
          {{ displaydate(date) }}
        </v-col>
      </v-row>
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="6"
          align="center"
        >
          filtres
        </v-col>
      </v-row>
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="6"
          align="center"
        >
          content
          <!--    <h3>{{ Results }}</h3>-->
          <!--          card use vdivider for middle line-->
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/simpleResults.json";
import TranslationsClient from "@clientTranslations/components/simpleResults.json";
import moment from "moment";
import 'moment/locale/fr';

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
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
    this.loading = true;
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
    //format date for axios request
    dateFormated(date) {
      return moment(new Date(date)).utcOffset("+00:00").format()
    },
    displaydate(date){
      return moment (new Date(date)).utcOffset("+00:00").format('ddd d MMMM YYYY')
    }
    //fill Results
  }
};
</script>


<style>
  .no-line-height {
    line-height: 0 !important;
  }
</style>
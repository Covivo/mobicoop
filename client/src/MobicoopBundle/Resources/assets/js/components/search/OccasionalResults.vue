<template>
  <v-content>
    <v-container
      fluid
    >
      <!--loading overlay-->
      <v-overlay 
        :value="loading"
      >
        <v-progress-circular
          indeterminate
          size="64"
        />
      </v-overlay>

      <!--Title row-->
      <v-row
        justify="start"
        align="center"
        class="mt-8"
        no-gutters
      >
        <v-col
          cols="3"
        />
        <v-col
          cols="2"
          class="title-size"
        >
          {{ origin }}
        </v-col>

        <v-icon
          :color="'yellow darken-2'"
          size="64"
          class="no-line-height ml-3 mr-5"
        >
          mdi-ray-start-end
        </v-icon>
        <v-col
          cols="2"
          class="title-size"
        >
          {{ destination }}
        </v-col>
        <v-col
          cols="auto"
        >
          <v-btn
            color="primary"
            fab
            small
            depressed
            dark
            class="ml-6-5"
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
      <!--      filter row-->
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="6"
          align-self="start"
        >
          <!--TODO : REMOVE WHEN START CODING FILTER COMPONENT-->
          <!-- <v-combobox
            v-model="chips"
            :items="items"
            chips
            clearable
            multiple
            label="Filtres"
          >
            <template v-slot:selection="{ attrs, item, select, selected }">
              <v-chip
                v-bind="attrs"
                :input-value="selected"
                close
                @click="select"
                @click:close="remove(item)"
              >
                <strong>{{ item }}</strong>&nbsp;
              </v-chip>
            </template>
          </v-combobox> -->
          <v-row
            justify="start"
            align="center"
            class="mt-8"
            no-gutters
          >
            <!-- <h3> {{ results.matchingRequests.length }} annonces trouvée(s) </h3> -->
            <!-- trouvée | trouvées -->
          </v-row>
        </v-col>
      </v-row>

      <v-row
        align="center"
      >
        <v-col
          cols="6"
          offset="3"
        >
          <!-- ResultCard  Compenent -->
          <result-card 
            :origin="origin"
            :destination="destination"
            :date="date"
            :carpool-results="results"
            :matching-search-url="matchingSearchUrl"
            :origin-latitude="originLatitude"
            :origin-longitude="originLongitude"
            :destination-latitude="destinationLatitude"
            :destination-longitude="destinationLongitude"
          />

          <!--card use vdivider for middle line-->
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
import ResultCard from "./ResultCard";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {ResultCard},
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
    }
  },
  data : function() {
    return {
      //results of the search
      results : null,
      loading : true,

      // TODO : REMOVE WHEN START CODING FILTER COMPONENT
      chips: ['Horaire', 'Type d\'annonce', 'Civilité'],
      items: ['Communauté', 'Espace'],
    };
  },
  created() {
    //fill Results
    this.loading = true;
    axios.get(this.matchingSearchUrl, {
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
        // console.error(response.data[0]);
        return this.results = response.data[0];
      })
      .catch((error) => {
        console.log(error);
      });
  },
  methods :{
    // TODO : REMOVE WHEN START CODING FILTER COMPONENT
    remove (item) {
      this.chips.splice(this.chips.indexOf(item), 1)
      this.chips = [...this.chips]
    },
    //format date for axios request
    dateFormated(date) {
      return moment(new Date(date)).utcOffset("+00:00").format()
    },  
    displaydate(date){
      return moment (new Date(date)).utcOffset("+00:00").format('ddd D MMMM YYYY')
    }
    //fill Results
  }
};
</script>


<style>
  .no-line-height {
    line-height: 0 !important;
  }
  .title-size{
    font-size: 18px;
    font-weight: bold;
  }

  .ml-6-5{
    margin-left: 26px;
  }
</style>
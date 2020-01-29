<template>
  <div>
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
          <h1>{{ $t('title') }}</h1>

          <!-- Matching header -->
          <matching-header
            v-if="!lProposalId"
            :origin="origin"
            :destination="destination"
            :date="date"
            :time="time"
            :regular="regular"
          />
          <!-- Matching filter -->
          <matching-filter 
            :communities="communities"
            @updateFilters="updateFilters" 
          />

          <!-- Number of matchings -->
          <v-row 
            justify="center"
            align="center"
          >
            <v-col
              v-if="!loading"
              cols="8"
              align="left"
            >
              {{ $tc('matchingNumber', numberOfResults, { number: numberOfResults }) }}
            </v-col>
            <v-col
              v-else
              cols="12"
              align="left"
            >
              {{ $t('search') }}
            </v-col>
            <v-col
              v-if="!loading && !newSearch"
              cols="4"
              align="end"
            >
              <v-btn
                rounded
                color="secondary"
                @click="startNewSearch()"
              >
                {{ $t('newSearch') }}
              </v-btn>
            </v-col>
          </v-row>
          <v-row v-if="newSearch">
            <v-col cols="12">
              <search
                :geo-search-url="geoSearchUrl"
                :user="user"
                :regular="regular"
                :hide-publish="true"
                :punctual-date-optional="true"
                :results="true"
                :default-destination="destination"
                :default-origin="origin"
                :default-outward-date="date"
              />
            </v-col>
          </v-row>

          <v-tabs v-model="modelTabs">
            <v-tab href="#carpools">
              {{ $t('tabs.carpools') }}
            </v-tab>
            <v-tab href="#otherCarpools">
              {{ $t('tabs.otherCarpools') }}
            </v-tab>
          </v-tabs>
          <v-tabs-items v-model="modelTabs">
            <v-tab-item value="carpools">
              <!-- Matching results -->
              <div v-if="loading">
                <v-row
                  v-for="n in 3"
                  :key="n"
                  class="text-left"
                >
                  <v-col cols="12">
                    <v-skeleton-loader
                      ref="skeleton"
                      type="article"
                      class="mx-auto"
                    />
                    <v-skeleton-loader
                      ref="skeleton"
                      type="actions"
                      class="mx-auto"
                    />
                  </v-col>
                </v-row>
              </div>
              <div v-else>
                <v-row 
                  v-for="(result,index) in results"
                  :key="index"
                  justify="center"
                >
                  <v-col
                    cols="12"
                    align="left"
                  >
                    <!-- Matching result -->
                    <matching-result
                      :result="result"
                      :user="user"
                      :distinguish-regular="distinguishRegular"
                      :carpooler-rate="carpoolerRate"
                      @carpool="carpool(result)"
                    />
                  </v-col>
                </v-row>
              </div>
            </v-tab-item>
            <v-tab-item value="otherCarpools">
              Les autres carpools
            </v-tab-item>
          </v-tabs-items>
        </v-col>
      </v-row>
    </v-container>

    <!-- carpool dialog -->
    <v-dialog
      v-model="carpoolDialog"
      max-width="800"
    >
      <matching-journey
        :result="result"
        :user="user"
        @close="carpoolDialog = false"
        @contact="contact"
        @carpool="launchCarpool"
      />
    </v-dialog>
  </div>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/carpool/results/Matching.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/Matching.json";
import MatchingHeader from "@components/carpool/results/MatchingHeader";
import MatchingFilter from "@components/carpool/results/MatchingFilter";
import MatchingResult from "@components/carpool/results/MatchingResult";
import MatchingJourney from "@components/carpool/results/MatchingJourney";
import Search from "@components/carpool/search/Search";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingHeader,
    MatchingFilter,
    MatchingResult,
    MatchingJourney,
    Search
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    // proposal Id if Matching results after an ad post
    proposalId: {
      type: Number,
      default: null
    },
    origin: {
      type: Object,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    time: {
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
    communityId: {
      type: Number,
      default: null
    },
    distinguishRegular: {
      type: Boolean,
      default: false
    },
    carpoolerRate: {
      type: Boolean,
      default: true
    },
    geoSearchUrl: {
      type: String,
      default: null
    }
    
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      carpoolDialog: false,
      proposal: null,
      result: null,
      loading : true,
      results: null,
      lOrigin: null,
      lDestination: null,
      lProposalId: this.proposalId,
      filters: null,
      newSearch: false,
      modelTabs:"carpools"
    };
  },
  computed: {
    numberOfResults() {
      return this.results ? Object.keys(this.results).length : 0 // ES5+
    },
    communities() {
      if (!this.results) return null;
      let communities = [];
      this.results.forEach((result,key) => {
        if (result.communities) {
          for (let key in result.communities) {  
            if (communities.indexOf(result.communities[key]) == -1) {
              communities.push({text:result.communities[key],value:key});    
            }
          }            
        }
      });
      return communities;
    }
  },
  created() {
    this.search();
  },
  methods :{
    carpool(result) {
      this.result = result;
      // open the dialog
      this.carpoolDialog = true;
    },
    search(){
    // if a proposalId is provided, we load the proposal results
      if (this.lProposalId) {
        this.loading = true;
        let postParams = {
          "filters": this.filters
        };
        axios.post(this.$t("proposalUrl",{id: Number(this.lProposalId)}),postParams,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
          .then((response) => {
            this.loading = false;
            this.results = response.data;
          })
          .catch((error) => {
            console.log(error);
          });
      } else {
      // otherwise we send a proposal search
        this.loading = true;
        let postParams = {
          "origin": this.origin,
          "destination": this.destination,
          "date": this.date,
          "time": this.time,
          "regular": this.regular,
          "userId": this.user ? this.user.id : null,
          "communityId": this.communityId,
          "filters": this.filters
        };
        axios.post(this.$t("matchingUrl"), postParams,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
          .then((response) => {
            this.loading = false;
            this.results = response.data;
            if (this.results[0].id) {
              this.lProposalId = this.results[0].id;
            }

          })
          .catch((error) => {
            console.log(error);
          });
      }

    },
    contact(params) {
      // console.log(params);
      axios.post(this.$t("contactUrl"), params,
        {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then((response) => {
          if(response.data=="ok"){
            window.location = this.$t("mailboxUrl");
          }
          else{
            console.log(response);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.carpoolDialog = false;
        })
    },
    launchCarpool(params) {
      // console.log(params);
      axios.post(this.$t("carpoolUrl"), params,
        {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then((response) => {
          if(response.data=="ok"){
            window.location = this.$t("mailboxUrl");
          }
          else{
            console.log(response);
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.carpoolDialog = false;
        })
    },
    updateFilters(data){
      this.filters = data;
      this.search();
    },
    startNewSearch() {
      this.newSearch = true;
    }
  }
};
</script>
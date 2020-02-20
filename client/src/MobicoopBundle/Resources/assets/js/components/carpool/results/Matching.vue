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
            :disabled-filters="loading"
            :disable-role="!this.includePassenger"
            @updateFilters="updateFilters" 
          />

          <!-- Number of matchings -->
          <v-row 
            justify="center"
            align="center"
          >
            <v-col
              v-if="!loading && !loadingExternal"
              :cols="(!newSearch) ? 8 : 12"
              class="text-left"
            >
              <p>{{ $tc('matchingNumber', numberOfResults, { number: numberOfResults }) }}</p>
              <p
                v-if="numberOfResults == 0 && !regular"
                class="font-weight-bold"
              >
                {{ $t('AskNewSearch') }}
              </p>
            </v-col>
            <v-col
              v-else
              cols="12"
            >
              {{ $t('search') }}
            </v-col>
            <v-col
              v-if="!loading && !loadingExternal && !newSearch"
              cols="4"
              align="end"
            >
              <v-btn
                v-if="!fromMyProposals"
                rounded
                color="secondary"
                @click="startNewSearch()"
              >
                {{ $t('newSearch') }}
              </v-btn>
            </v-col>
          </v-row>
          <v-row v-if="!fromMyProposals">
            <v-col
              cols="12"
              class="text-left"
            >
              <v-switch
                v-if="role!=3"
                v-model="includePassenger"
                class="ma-2"
                label="Voir aussi les annonces passager"
              />
              <v-alert
                v-else
                class="accent white--text"
                dense
                dismissible
              >
                Cette recherche contient également les annonces passager
              </v-alert>
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

          <v-tabs
            v-if="externalRdexJourneys"
            v-model="modelTabs"
          >
            <v-tab href="#carpools">
              <v-badge
                color="primary"
                :content="nbCarpoolPlatform"
                icon="mdi-timer-sand"
              >              
                {{ $t('tabs.carpools', {'platform':platformName}) }}
              </v-badge>
            </v-tab>
            <v-tab
              v-if="externalRdexJourneys"
              href="#otherCarpools"
            >
              <v-badge
                color="primary"
                :content="nbCarpoolOther"
                icon="mdi-timer-sand"
              >              
                {{ $t('tabs.otherCarpools') }}
              </v-badge>
            </v-tab>
          </v-tabs>
          <v-tabs-items v-model="modelTabs">
            <v-tab-item value="carpools">
              <matching-results
                :results="results"
                :distinguish-regular="distinguishRegular"
                :carpooler-rate="carpoolerRate"
                :user="user"
                :loading-prop="loading"
                @carpool="carpool"
              />
            </v-tab-item>
            <v-tab-item
              v-if="externalRdexJourneys"
              value="otherCarpools"
            >
              <matching-results
                :results="externalRDEXResults"
                :distinguish-regular="distinguishRegular"
                :carpooler-rate="carpoolerRate"
                :user="user"
                :loading-prop="loadingExternal"
                :external-rdex-journeys="externalRdexJourneys"
                @carpool="carpool"
              />
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
import MatchingResults from "@components/carpool/results/MatchingResults";
import MatchingJourney from "@components/carpool/results/MatchingJourney";
import Search from "@components/carpool/search/Search";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingHeader,
    MatchingFilter,
    MatchingResults,
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
    },
    externalRdexJourneys: {
      type: Boolean,
      default: false
    },
    platformName: {
      type: String,
      default: ""
    },
    defaultRole:{
      type: Number,
      default: 3
    }
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      carpoolDialog: false,
      proposal: null,
      results: null,
      externalRDEXResults:null,
      result: null,
      loading : true,
      loadingExternal : false,
      lOrigin: null,
      lDestination: null,
      lProposalId: this.proposalId,
      filters: null,
      newSearch: false,
      modelTabs:"carpools",
      nbCarpoolPlatform:0,
      nbCarpoolOther:0,
      role:this.defaultRole,
      includePassenger:false,
      fromMyProposals:false
    };
  },
  computed: {
    numberOfResults() {
      let numberOfResults = 0;
      (!isNaN(this.nbCarpoolPlatform)) ? numberOfResults = numberOfResults + this.nbCarpoolPlatform : 0;
      (!isNaN(this.nbCarpoolOther)) ? numberOfResults = numberOfResults + this.nbCarpoolOther : 0;
      return numberOfResults;
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
  watch:{
    includePassenger(){
      if(this.includePassenger){
        this.role = 3;
        this.lProposalId = null;
      }
      else{
        this.role = 2;
      }
      this.search();
    }
  },
  created() {
    if(this.proposalId) this.fromMyProposals = true;
    this.search();
    if(this.externalRdexJourneys) this.searchExternalJourneys();
  },
  methods :{
    carpool(carpool) {
      this.result = carpool;
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
            (response.data.length>0) ? this.nbCarpoolPlatform = response.data.length : this.nbCarpoolPlatform = "-";
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
          "filters": this.filters,
          "role": this.role
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
            if (this.results.length>0 && this.results[0].id) {
              this.lProposalId = this.results[0].id;
              this.nbCarpoolPlatform = this.results.length;
            }
            else{
              this.nbCarpoolPlatform = "-";
            }

          })
          .catch((error) => {
            console.log(error);
          });
      }

    },
    searchExternalJourneys(){
      this.loadingExternal = true;
      let postParams = {
        "driver": 1, // TO DO : Dynamic
        "passenger": 0, // TO DO : Dynamic
        "from_latitude": this.origin.latitude,
        "from_longitude": this.origin.longitude,
        "to_latitude": this.destination.latitude,
        "to_longitude": this.destination.longitude
      };
      axios.post(this.$t("externalJourneyUrl"), postParams,
        {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then((response) => {
          this.loadingExternal = false;
          this.externalRDEXResults = response.data;
          (response.data.length>0) ? this.nbCarpoolOther = response.data.length : this.nbCarpoolOther = '-';
        })
        .catch((error) => {
          console.log(error);
        });

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
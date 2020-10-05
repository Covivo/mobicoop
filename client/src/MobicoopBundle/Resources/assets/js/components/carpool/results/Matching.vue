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
            v-if="!lProposalId && !lExternalId"
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
            :disable-role="!includePassenger"
            :default-community-id="lCommunityId"
            :init-filters-chips="initFiltersChips"
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
              <v-alert
                text
                color="success"
              >
                {{ $t('search') }}
                <v-progress-linear
                  indeterminate
                />
              </v-alert>
            </v-col>
            <v-col
              v-if="!loading && !loadingExternal && !newSearch"
              cols="4"
              align="end"
            >
              <v-btn
                v-if="displayNewSearch"
                rounded
                color="secondary"
                @click="startNewSearch()"
              >
                {{ $t('newSearch') }}
              </v-btn>
            </v-col>
          </v-row>

          <!-- Include passengers switch -->
          <v-row v-if="displayNewSearch">
            <v-col
              v-if="!loading"
              cols="12"
              class="text-left"
            >
              <v-switch
                v-if="role!=3"
                v-model="includePassenger"
                class="ma-2"
                :label="$t('includePassengers')"
              />
              <v-alert
                v-else
                class="accent white--text"
                dense
                dismissible
              >
                {{ $t('alsoIncludePassengers') }}
              </v-alert>
            </v-col>
          </v-row>

          <!-- New search -->
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

          <!-- Tabs : platform results, external results, public transport results -->
          <v-tabs
            v-if="displayTab"
            v-model="modelTabs"
          >
            <!-- Platform results tab -->
            <v-tab href="#carpools">
              <v-badge
                color="primary"
                :content="nbCarpoolPlatform"
                icon="mdi-timer-sand"
              >              
                {{ $t('tabs.carpools', {'platform':platformName}) }}
              </v-badge>
            </v-tab>
            <!-- External results tab -->
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
            <!-- Public transport results tab -->
            <v-tab
              v-if="ptSearch"
              href="#ptSearch"
            >
              <v-badge
                color="primary"
                :content="nbPtResults"
                icon="mdi-timer-sand"
              >              
                {{ $t('tabs.ptresults') }}
              </v-badge>
            </v-tab>            
          </v-tabs>
          <!-- Tabs items  -->
          <v-tabs-items v-model="modelTabs">
            <!-- Platform results tab item -->
            <v-tab-item value="carpools">
              <matching-results
                :results="results"
                :nb-results="nbCarpoolPlatform"
                :distinguish-regular="distinguishRegular"
                :carpooler-rate="carpoolerRate"
                :user="user"
                :loading-prop="loading"
                :page="page"
                @carpool="carpool"
                @loginOrRegister="loginOrRegister"
                @paginate="paginate"
              />
            </v-tab-item>
            <!-- External results tab item -->
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
            <!-- Public transport results tab item -->
            <v-tab-item
              v-if="ptSearch"
              value="ptSearch"
            >
              <MatchingPTResults
                :pt-results="ptResults"
                :loading-pt-results="loadingPtResults"
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
        :reset-step="resetStepMatchingJourney"
        @close="carpoolDialog = false"
        @contact="contact"
        @carpool="launchCarpool"
        @resetStepMatchingJourney="resetStepMatchingJourney = false"
      />
    </v-dialog>
    
    <!-- login or register dialog -->
    <v-dialog
      v-model="loginOrRegisterDialog"
      max-width="800"
    >
      <v-card>
        <v-toolbar
          color="primary"
        >
          <v-toolbar-title class="toolbar">
            {{ $t('loginOrRegisterTitle') }}
          </v-toolbar-title>
        
          <v-spacer />

          <v-btn 
            icon
            @click="loginOrRegisterDialog = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text>
          <p class="text--primary ma-1">
            {{ $t('loginOrRegister') }}
          </p>
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn
            rounded
            color="secondary"
            large
            :href="$t('loginUrl',{'id':lProposalId})"
          >
            <span>
              {{ $t('login') }}
            </span>
          </v-btn>
          <v-btn
            rounded
            color="secondary"
            large
            :href="$t('registerUrl',{'id':lProposalId})"
          >
            <span>
              {{ $t('register') }}
            </span>
          </v-btn>
        </v-card-actions>
      </v-card>
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
import MatchingPTResults from "@components/carpool/results/publicTransport/MatchingPTResults";
import Search from "@components/carpool/search/Search";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingHeader,
    MatchingFilter,
    MatchingResults,
    MatchingJourney,
    Search,
    MatchingPTResults
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
    // external Id after external search
    externalId: {
      type: String,
      default: null
    },
    // limit the result to the given matching proposal Id
    // NOT USED YET
    targetProposalId: {
      type: String,
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
    },
    ptSearch: {
      type: Boolean,
      default: false
    }
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      carpoolDialog: false,
      loginOrRegisterDialog: false,
      results: null,
      externalRDEXResults:null,
      result: null,
      ptResults:null,
      loading : true,
      loadingExternal : false,
      lProposalId: this.proposalId ? this.proposalId : null,
      lExternalId: this.externalId ? this.externalId : null,
      loadingPtResults : false,
      lOrigin: null,
      lDestination: null,
      filters: null,
      newSearch: false,
      modelTabs:"carpools",
      nbCarpoolPlatform:0,
      nbCarpoolOther:0,
      nbPtResults:0,
      role:this.defaultRole,
      includePassenger:false,
      displayNewSearch:true,
      initFiltersChips:false,
      lCommunityId: this.communityId,
      lCommunityIdBak: this.communityId,
      resetStepMatchingJourney: false,
      page:1
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
    },
    displayTab(){
      return (this.externalRdexJourneys || this.ptSearch) ? true : false;
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
    },
    communities(){
      this.initFiltersChips = true;
    },
    lCommunityId(){
      this.lCommunityIdBak = this.lCommunityId;
    }
  },
  created() {
    if(this.proposalId) this.displayNewSearch = false;
    this.search();
    if(this.externalRdexJourneys) this.searchExternalJourneys();
    if(this.ptSearch) this.searchPTJourneys();
  },
  methods :{
    carpool(carpool) {
      this.result = carpool;
      // open the dialog
      this.carpoolDialog = true;
      this.resetStepMatchingJourney = true;
    },
    loginOrRegister(carpool) {
      this.result = carpool;
      // open the dialog
      this.loginOrRegisterDialog = true;
    },
    paginate(page) {
      this.page = page;
      this.search();
    },
    login() {
      
    },
    register() {
      
    },
    search(){
      // if a proposalId is provided, we load the proposal results
      if (this.lProposalId) {
        this.loading = true;
        if (this.filters === null) {
          this.filters = {
            "page": this.page
          };
        } else {
          this.filters.page = this.page;
        }
        let postParams = {
          "filters": this.filters,
        };
        axios.post(this.$t("proposalUrl",{id: Number(this.lProposalId)}),postParams,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
          .then((response) => {
            this.loading = false;
            this.results = response.data.results;
            this.nbCarpoolPlatform = response.data.nb;
          })
          .catch((error) => {
            console.log(error);
          });
      } else if (this.lExternalId) {
        // if an externalId is provided, we load the corresponding proposal results
        this.loading = true;
        if (this.filters === null) {
          this.filters = {
            "page": this.page
          };
        } else {
          this.filters.page = this.page;
        }
        let postParams = {
          "filters": this.filters
        };
        axios.post(this.$t("externalUrl",{id: this.lExternalId}),postParams,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
          .then((response) => {
            this.loading = false;
            this.results = response.data.results;
            this.nbCarpoolPlatform = response.data.nb;
            if (this.results.length>0 && this.results[0].id) {
              this.lProposalId = this.results[0].id;
            }
            
          })
          .catch((error) => {
            console.log(error);
          });
      } else {
      // otherwise we send a proposal search
        this.loading = true;
        if (this.filters === null) {
          this.filters = {
            "page": this.page
          };
        } else {
          this.filters.page = this.page;
        }
        let postParams = {
          "origin": this.origin,
          "destination": this.destination,
          "date": this.date,
          "time": this.time,
          "regular": this.regular,
          "userId": this.user ? this.user.id : null,
          "communityId": this.lCommunityId,
          "filters": this.filters,
          "role": this.role
        };
        var start = new Date();
        axios.post(this.$t("matchingUrl"), postParams,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
          .then((response) => {
            this.loading = false;
            this.results = response.data.results;
            this.nbCarpoolPlatform = response.data.nb;
            if (this.results.length>0 && this.results[0].id) {
              this.lProposalId = this.results[0].id;
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
    searchPTJourneys(){
      this.loadingPtResults = true;
      let postParams = {
        "from_latitude": this.origin.latitude,
        "from_longitude": this.origin.longitude,
        "to_latitude": this.destination.latitude,
        "to_longitude": this.destination.longitude,
        "date": this.date
      };
      axios.post(this.$t("ptSearchUrl"), postParams,
        {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then((response) => {
          //console.error(response.data);
          this.loadingPtResults = false;
          (response.data.member) ? this.ptResults = response.data.member : this.ptResults = [];
          (response.data.member && response.data.member.length>0) ? this.nbPtResults = response.data.member.length : this.nbPtResults = '-';
        })
        .catch((error) => {
          console.log(error);
        });
    },
    contact(params) {
      axios.post(this.$t("contactUrl"), params,
        {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then((response) => {
          if(response.status == 200){
            window.location = this.$t("mailboxUrl", {'askId':response.data.askId});
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
      axios.post(this.$t("carpoolUrl"), params,
        {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then((response) => {
          if(response.status == 200){
            window.location = this.$t("mailboxUrl", {'askId':response.data.askId});
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
      this.page=1;
      this.filters = data;
      // Update the default filters also
      this.lCommunityId = (this.filters.filters.community) ? parseInt(this.filters.filters.community) : null;

      // If the communityid for a research has been modified, we need to post a new proposal for the search
      // We don't use the watch because it's excuted after updateFilters() is done (after the this.search...)
      if(this.lCommunityId !== this.lCommunityIdBak){
        this.lProposalId = null;
      }
      this.search();
    },
    startNewSearch() {
      this.newSearch = true;
    }
  }
};
</script>
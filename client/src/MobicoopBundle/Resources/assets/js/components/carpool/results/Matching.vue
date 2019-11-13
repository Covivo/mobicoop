<template>
  <div>
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
            :time="time"
            :regular="regular"
          />

          <!-- Matching filter -->
          <matching-filter />

          <!-- Number of matchings -->
          <v-row 
            justify="center"
            align="center"
          >
            <v-col
              v-if="!loading"
              cols="12"
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
          </v-row>

          <!-- Matching results -->
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

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingHeader,
    MatchingFilter,
    MatchingResult,
    MatchingJourney
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
    }
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      carpoolDialog: false,
      proposal: null,
      result: null,
      loading : true,
      results: null
    };
  },
  computed: {
    numberOfResults() {
      return this.results ? Object.keys(this.results).length : 0 // ES5+
    }
  },
  created() {
    // if a proposalId is provided, we load the proposal results
    if (this.proposalId) {
      this.loading = true;
      axios.get(this.$t("proposalUrl",{id: Number(this.proposalId)}))
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
        "communityId": this.communityId
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
        })
        .catch((error) => {
          console.log(error);
        });
    }
  },
  methods :{
    carpool(result) {
      this.result = result;
      // open the dialog
      this.carpoolDialog = true;
    },
    // TODO : REMOVE WHEN START CODING FILTER COMPONENT
    remove (item) {
      this.chips.splice(this.chips.indexOf(item), 1)
      this.chips = [...this.chips]
    },
    contact(params) {
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
      //console.log(params);
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
    }
  }
};
</script>
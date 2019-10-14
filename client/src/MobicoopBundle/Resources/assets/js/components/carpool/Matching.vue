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
            :time="time"
            :regular="regular"
          />

          <!-- Matching filter -->
          <matching-filter />

          <!-- Matching results -->
          <matching-results
            :origin="origin"
            :destination="destination"
            :date="date"
            :time="time"
            :regular="regular"
            :user="user"
            @carpool="carpool"
          />
        </v-col>
      </v-row>
    </v-container>

    <!-- carpool dialog -->
    <v-dialog
      v-model="carpoolDialog"
      max-width="800"
    >
      <matching-journey
        :origin="origin"
        :destination="destination"
        :user="user"
        :date="date"
        :regular="regular"
        :matching="matching"
      />
    </v-dialog>
  </v-content>
</template>
<script>

// import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/Matching.json";
import TranslationsClient from "@clientTranslations/components/carpool/Matching.json";
import MatchingHeader from "./MatchingHeader";
import MatchingFilter from "./MatchingFilter";
import MatchingResults from "./MatchingResults";
import MatchingJourney from "./MatchingJourney";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    MatchingHeader,
    MatchingFilter,
    MatchingResults,
    MatchingJourney
  },
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
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
    }
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      carpoolDialog: false,
      matching: null
    };
  },
  methods :{
    carpool(params) {
      this.matching = params.matching;

      // open the dialog
      this.carpoolDialog = true;

      // axios.post(this.$t("contactUrl"), {
      //   "proposalId":params.proposal.id,
      //   "origin": this.origin,
      //   "destination": this.destination,
      //   "date": params.date,
      //   "time": params.time,
      //   "priceKm": params.proposal.criteria.priceKm,
      //   "driver": params.driver,
      //   "passenger": params.passenger,
      //   "regular": this.regular
      // },
      // {
      //   headers:{
      //     'content-type': 'application/json'
      //   }
      // })
      //   .then((response) => {
      //     if(response.data=="ok"){
      //       //this.emitSnackbar('snackBar.success','success')
      //       window.location = "/utilisateur/messages";
      //     }
      //     else{
      //       //this.emitSnackbar('snackBar.error','error')
      //     }
      //   })
      //   .catch((error) => {
      //     console.log(error);
      //     //this.emitSnackbar('snackBar.error','error')
      //   })
      //   .finally(() => {
      //     //this.loading = false;
      //   })
    },
    // TODO : REMOVE WHEN START CODING FILTER COMPONENT
    remove (item) {
      this.chips.splice(this.chips.indexOf(item), 1)
      this.chips = [...this.chips]
    },
  }
};
</script>

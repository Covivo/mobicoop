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
  </v-content>
</template>
<script>

import axios from "axios";
import moment from "moment";
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
    };
  },
  methods :{
    carpool(params) {
      axios.get(this.$t("contactUrl"), {
        params:{
          "proposalId":params.proposal.id,
          "origin_streetAddress": this.originStreetAddress,
          "origin_addressLocality": this.originAddressLocality,
          "destination_streetAddress": this.destinationStreetAddress,
          "destination_addressLocality": this.destinationAddressLocality,
          "origin_latitude": Number(this.originLatitude),
          "origin_longitude": Number(this.originLongitude),
          "destination_latitude": Number(this.destinationLatitude),
          "destination_longitude": Number(this.destinationLongitude),
          "date": this.date,
          "priceKm": params.proposal.criteria.priceKm,
          "driver": params.driver,
          "passenger": params.passenger,
          "regular": this.regular
        }
      })
        .then((response) => {
          if(response.data=="ok"){
            //this.emitSnackbar('snackBar.success','success')
            window.location = "/utilisateur/messages";
          }
          else{
            //this.emitSnackbar('snackBar.error','error')
          }
        })
        .catch((error) => {
          console.log(error);
          //this.emitSnackbar('snackBar.error','error')
        })
        .finally(() => {
          //this.loading = false;
        })
    },
    // TODO : REMOVE WHEN START CODING FILTER COMPONENT
    remove (item) {
      this.chips.splice(this.chips.indexOf(item), 1)
      this.chips = [...this.chips]
    },
  }
};
</script>
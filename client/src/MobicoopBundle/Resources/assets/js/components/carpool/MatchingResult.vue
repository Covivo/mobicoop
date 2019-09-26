<template>
  <v-card
    outlined
  >
    <v-container>
      <v-row
        justify="start"
        align="center"
        dense
      >
        <!-- Role -->
        <v-col
          cols="2"
          align="center"
        >
          <v-icon
            v-if="driver"
            color="primary"
            :size="!passenger ? '75' : '40'"
          >
            mdi-car
          </v-icon>

          <v-icon
            v-if="passenger"
            color="primary"
            :size="!driver ? '75' : '40'"
          >
            mdi-walk
          </v-icon>
        </v-col>

        <!-- Detail -->
        <v-col
          cols="10"
        >
          <!-- Regular : summary of days -->
          <days-summary
            v-if="regular"
            :proposal="driver ? matching.proposalOffer : matching.proposalRequest"
          />

          <v-divider v-if="regular" /> 

          <!-- Journey summary : date, time, summary of route, seats, price -->
          <journey-summary 
            :driver="driver"
            :passenger="passenger"
            :proposal="driver ? matching.proposalOffer : matching.proposalRequest"
            :regular="regular"
            :user="user"
          />

          <v-divider /> 

          <!-- Carpooler detail -->
          <carpooler-summary 
            :user="driver ? matching.proposalOffer.user : matching.proposalRequest.user"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/MatchingResult.json";
import TranslationsClient from "@clientTranslations/components/carpool/MatchingResult.json";
import DaysSummary from "../utilities/DaysSummary"
import JourneySummary from "../utilities/JourneySummary"
import CarpoolerSummary from "../utilities/CarpoolerSummary"

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    DaysSummary,
    JourneySummary,
    CarpoolerSummary
  },
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
    matching: {
      type:Object,
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
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
    }
  },
  computed: {
    driver() {
      // a user is driver if he is the owner of the proposalOffer
      return this.matching.proposalOffer.user ? true : false
    },
    passenger() {
      // a user is passenger if he is the owner of the proposalRequest or if he is also passenger for his proposalOffer
      return (this.matching.proposalRequest.user || this.matching.proposalOffer.criteria.passenger) ? true : false
    }
  },
  methods :{
  }
};
</script>
<style>
</style>
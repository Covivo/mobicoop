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
            v-if="showRegularSummary"
            :proposal="driver ? matching.offer.proposalOffer : matching.request.proposalRequest"
          />

          <v-divider v-if="showRegularSummary" /> 

          <!-- Journey summary : date, time, summary of route, seats, price -->
          <journey-summary 
            :matching="matching"
            :regular="regular"
            :user="user"
            :date="driver ? matching.offer.criteria.fromDate : matching.request.criteria.fromDate"
          />

          <v-divider /> 

          <!-- Carpooler detail -->
          <carpooler-summary 
            :user="user"
            :carpooler="driver ? matching.offer.proposalOffer.user : matching.request.proposalRequest.user"
            :proposal="driver ? matching.offer.proposalOffer : matching.request.proposalRequest"
            :matching="matching"
            @carpool="carpool"
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
    date: {
      type: String,
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
    showRegularSummary() {
      if (this.driver) {
        if (this.matching.offer.proposalOffer.criteria.frequency == 2) return true;
      }
      if (this.passenger) {
        if (this.matching.request.proposalRequest.criteria.frequency == 2) return true;
      }
      return false;
    },
    driver() {
      // the matching user is driver if he has an offer
      return this.matching.offer ? true : false
    },
    passenger() {
      // the matching user is driver if he has a request
      return this.matching.request ? true : false
    }
  },
  methods :{
    carpool(params) {
      this.$emit("carpool", {
        proposal: params.proposal,
        // warning : we set below the possible roles of the requester, so the opposite ones of the current carpooler !
        driver: this.passenger,
        passenger: this.driver,
        // we send the date of the carpool
        date: this.getCarpoolDate(params),
        // we send the time of the carpool
        time: this.getCarpoolTime(params)
      });
    },
    // this methods gives the date of the carpool
    getCarpoolDate(params) {
      // if the search is regular, it's the selected date
      if (this.regular) return this.date;
      // if the search is punctual
      if (params.proposal.criteria.frequency == 1) {
        // it's the date of the matching proposal if the matching proposal is punctual
        return params.proposal.criteria.fromDate;
      } 
      // TODO : it's the date of the first matching date if it's a regular matching proposal
      // for now we return the selected date...
      return this.date;
    },
    // this methods gives the time of the carpool
    getCarpoolTime(params) {
      // if the search is regular, the time is null
      if (this.regular) return null;
      // if the search is punctual
      if (this.passenger) {
        // here the requester is the driver, the current carpooler is passenger
        // the time is the time of the current carpooler start
      }
      if (this.driver) {
        // we have to return the time 
      }
    }
  }
};
</script>
<style>
</style>
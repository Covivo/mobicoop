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
            v-if="result.resultPassenger"
            color="primary"
            :size="!result.resultDriver ? '75' : '40'"
          >
            mdi-car
          </v-icon>

          <v-icon
            v-if="result.resultDriver"
            color="primary"
            :size="!result.resultPassenger ? '75' : '40'"
          >
            mdi-walk
          </v-icon>
        </v-col>

        <!-- Detail -->
        <v-col
          cols="10"
        >
          <!-- Regular : summary of days -->
          <regular-planning-summary
            v-if="showRegularSummary"
            :mon-active="result.monCheck"
            :tue-active="result.tueCheck"
            :wed-active="result.wedCheck"
            :thu-active="result.thuCheck"
            :fri-active="result.friCheck"
            :sat-active="result.satCheck"
            :sun-active="result.sunCheck"
            :outward-time="result.outwardTime"
            :return-time="result.returnTime"
            :return-trip="result.return"
          />

          <v-divider v-if="showRegularSummary" />

          <!-- Journey summary : date, time, summary of route, seats, price -->
          <journey-summary
            :origin="result.origin"
            :origin-first="result.originFirst"
            :destination="result.destination"
            :destination-last="result.destinationLast"
            :date="result.date"
            :time="result.time"
            :seats="result.seats"
            :price="result.roundedPrice"
          />

          <v-divider />

          <!-- Carpooler detail -->
          <carpooler-summary
            :carpooler="result.carpooler"
            :carpooler-rate="carpoolerRate"
            :user="user"
            :external-rdex-journeys="externalRdexJourneys"
            :external-url="(result.externalUrl) ? result.externalUrl : null"
            :external-origin="(result.externalOrigin) ? result.externalOrigin : null"
            @carpool="carpool"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/carpool/results/MatchingResult.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/MatchingResult.json";
import RegularPlanningSummary from "@components/carpool/utilities/RegularPlanningSummary"
import JourneySummary from "@components/carpool/utilities/JourneySummary"
import CarpoolerSummary from "@components/carpool/utilities/CarpoolerSummary"

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    RegularPlanningSummary,
    JourneySummary,
    CarpoolerSummary
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    result: {
      type:Object,
      default: null
    },
    user: {
      type:Object,
      default: null
    },
    // show regular journeys results as regular journeys in case of punctual search
    // if not, the regular journey will be displayed as a punctual journey
    distinguishRegular: {
      type: Boolean,
      default: false
    },
    carpoolerRate: {
      type: Boolean,
      default: true
    },
    externalRdexJourneys: {
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
      return (this.result.frequency == 2 || (this.distinguishRegular && this.result.frequencyResult == 2));
    }
  },
  methods :{
    carpool() {
      this.$emit("carpool", {
        //matching: this.matching
      });
    },

  }
};
</script>
<style>
</style>

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
            :pick-up-outward="result.pickUpOutward"
            :pick-up-return="result.pickUpReturn"
          />

          <v-divider v-if="showRegularSummary" />

          <!-- Journey summary : date, time, summary of route, seats, price -->
          <journey-summary
            :origin="result.origin"
            :origin-first="result.originFirst"
            :destination="result.destination"
            :destination-last="result.destinationLast"
            :pick-up="result.pickUpOutward"
            :date="result.date"
            :time="result.time"
            :seats="result.seats"
            :price="result.roundedPrice"
            :solidary-exclusive="result.solidaryExclusive"
            :hide-pick-up="externalRdexJourneys"
          />

          <v-divider />

          <!-- Carpooler detail -->
          <carpooler-summary
            :carpooler="result.carpooler"
            :user="user"
            :external-rdex-journeys="externalRdexJourneys"
            :external-url="(result.externalUrl) ? result.externalUrl : null"
            :external-origin="(result.externalOrigin) ? result.externalOrigin : null"
            :external-provider="(result.externalProvider) ? result.externalProvider : null"
            :external-journey-id="(result.externalJourneyId) ? result.externalJourneyId : null"
            :communities="result.communities"
            :origin="result.origin"
            :destination="result.destination"
            :age-display="ageDisplay"
            @carpool="carpool"
            @loginOrRegister="loginOrRegister"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
import {messages_en, messages_fr, messages_eu} from "@translations/components/carpool/results/MatchingResult/";
import RegularPlanningSummary from "@components/carpool/utilities/RegularPlanningSummary"
import JourneySummary from "@components/carpool/utilities/JourneySummary"
import CarpoolerSummary from "@components/carpool/utilities/CarpoolerSummary"

export default {
  components: {
    RegularPlanningSummary,
    JourneySummary,
    CarpoolerSummary
  },
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
    externalRdexJourneys: {
      type: Boolean,
      default: false
    },
    communities: {
      type: Object,
      default: null
    },
    ageDisplay: {
      type: Boolean,
      default: false
    }
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
    loginOrRegister() {
      this.$emit("loginOrRegister", {
        //matching: this.matching
      });
    }
  }
};
</script>
<style>
</style>

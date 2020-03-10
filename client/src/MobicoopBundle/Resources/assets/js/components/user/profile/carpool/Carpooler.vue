<template>
  <v-container>
    <v-row justify="center">
      <v-col
        class="pa-0"
      >
        <v-container :class=" isRegular ? 'primary lighten-5 py-0' : 'py-0'">
          <v-row class="py-0">
            <v-col
              class="py-0"
            >
              <route-summary
                :origin="result.origin"
                :destination="result.destination"
                :type="result.frequency"
                :time="!isRegular ? result.time : null"
                :regular="isRegular"
                :compact="true"
                text-color-class="primary--text"
                icon-color="accent"
              />
            </v-col>
          </v-row>
        </v-container>
      </v-col>
    </v-row>
    <v-row v-if="isRegular">
      <v-col cols="5">
        <regular-days-summary
          :mon-active="hasMonday"
          :tue-active="hasTuesday"
          :wed-active="hasWednesday"
          :thu-active="hasThursday"
          :fri-active="hasFriday"
          :sat-active="hasSaturday"
          :sun-active="hasSunday"
          :date-start-of-validity="result.fromDate"
          :date-end-of-validity="result.toDate"
        />
      </v-col>

      <v-col class="py-0">
        <schedules
          :outward-times="outwardTimes"
          :return-times="returnTimes"
          :is-return="hasReturn"
          :is-regular="isRegular"
        />
      </v-col>
    </v-row>
    <v-row
      no-gutters
      align="center"
    >
      <v-col
        cols="7"
      >
        <carpooler-identity
          :carpooler="result.carpooler"
        />
      </v-col>
      <v-col
        cols="3"
      >
        <!--display phone is always true when ask is accepted-->
        <carpooler-contact
          :carpooler="result.carpooler"
          :ask="result.ask"
          :display-phone="true"
          :display-mail-box="true"
          :user="user"
        />
      </v-col>
      <!--      <v-col-->
      <!--        cols="2"-->
      <!--        class="text-right"-->
      <!--      >-->
      <!--        <v-btn-->
      <!--          rounded-->
      <!--          depressed-->
      <!--          color="secondary"-->
      <!--          class="text-none"-->
      <!--          height="40px"-->
      <!--        >-->
      <!--          {{ $t('ui.button.cancel') }}-->
      <!--        </v-btn>-->
      <!--      </v-col>-->
      <v-col
        v-if="isDriver"
        cols="2"
        class="font-weight-bold primary--text headline text-right"
      >
        {{ result.roundedPrice }}€
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/carpool/CarpoolFooter.js";
import TranslationsClient from "@clientTranslations/components/user/profile/carpool/CarpoolFooter.js";

import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
import CarpoolerIdentity from "@components/carpool/utilities/CarpoolerIdentity";
import CarpoolerContact from "@components/carpool/utilities/CarpoolerContact";
import Schedules from '@components/user/profile/ad/Schedules.vue';

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    CarpoolerIdentity,
    CarpoolerContact,
    RegularDaysSummary,
    RouteSummary,
    Schedules
  },
  props: {
    ad: {
      type: Object,
      required: true
    },
    result: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      //todo: use correct times from result/result schedule object when it's developed
      outwardTimes: this.ad ? [
        this.ad.schedule.monOutwardTime,
        this.ad.schedule.tueOutwardTime,
        this.ad.schedule.wedOutwardTime,
        this.ad.schedule.thuOutwardTime,
        this.ad.schedule.friOutwardTime,
        this.ad.schedule.satOutwardTime,
        this.ad.schedule.sunOutwardTime
      ].filter(Boolean) : [],
      returnTimes: this.ad ? [
        this.ad.schedule.monReturnTime,
        this.ad.schedule.tueReturnTime,
        this.ad.schedule.wedReturnTime,
        this.ad.schedule.thuReturnTime,
        this.ad.schedule.friReturnTime,
        this.ad.schedule.satReturnTime,
        this.ad.schedule.sunReturnTime
      ].filter(Boolean) : []
    }
  },
  computed: {
    isDriver() {
      return this.ad.role === 1 || this.ad.role === 3
    },
    isPassenger() {
      return this.ad.role === 2 || this.ad.role === 3
    },
    hasReturn () {
      return !this.ad.oneWay;
    },
    isRegular () {
      return this.ad.frequency === 2;
    },
    hasMonday () {
      return this.result && this.result.monCheck;
    },
    hasTuesday () {
      return this.result && this.result.tueCheck;
    },
    hasWednesday () {
      return this.result && this.result.wedCheck;
    },
    hasThursday () {
      return this.result && this.result.thuCheck;
    },
    hasFriday () {
      return this.result && this.result.friCheck;
    },
    hasSaturday () {
      return this.result && this.result.satCheck;
    },
    hasSunday () {
      return this.result && this.result.sunCheck;
    }
  },
}
</script>

<style scoped>

</style>
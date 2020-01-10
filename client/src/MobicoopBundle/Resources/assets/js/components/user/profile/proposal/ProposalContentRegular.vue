<template>
  <v-container
    fluid
    class="pa-0"
  >
    <v-row>
      <v-col cols="5">
        <regular-days-summary
          :mon-active="hasMonday"
          :tue-active="hasTuesday"
          :wed-active="hasWednesday"
          :thu-active="hasThursday"
          :fri-active="hasFriday"
          :sat-active="hasSaturday"
          :sun-active="hasSunday"
          :date-end-of-validity="proposal.outward.outwardLimitDate"
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
    <v-row justify="center">
      <v-col
        cols="12"
        class="py-0"
      >
        <v-container class="primary lighten-5">
          <v-row>
            <v-col
              class="py-0"
            >
              <route-summary
                :origin="proposal.outward.outwardWaypoints[0].address"
                :destination="proposal.outward.outwardWaypoints[proposal.outward.outwardWaypoints.length - 1].address"
                :type="proposal.outward.frequency"
                :regular="isRegular"
                text-color-class="primary--text text--darken-2"
                icon-color="accent"
              />
            </v-col>
          </v-row>
        </v-container>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { isEmpty } from "lodash";

import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
import Schedules from '@components/user/profile/proposal/Schedules.vue';

export default {
  components: {
    RegularDaysSummary,
    RouteSummary,
    Schedules
  },
  props: {
    proposal: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      outwardTimes: this.proposal.outward ? [
        this.proposal.outward.schedule.monOutwardTime, 
        this.proposal.outward.schedule.tueOutwardTime, 
        this.proposal.outward.schedule.wedOutwardTime, 
        this.proposal.outward.schedule.thuOutwardTime, 
        this.proposal.outward.schedule.friOutwardTime, 
        this.proposal.outward.schedule.satOutwardTime, 
        this.proposal.outward.schedule.sunOutwardTime
      ].filter(Boolean) : [],
      returnTimes: this.proposal.return ? [
        this.proposal.return.schedule.monReturnTime,
        this.proposal.return.schedule.tueReturnTime,
        this.proposal.return.schedule.wedReturnTime,
        this.proposal.return.schedule.thuReturnTime,
        this.proposal.return.schedule.friReturnTime,
        this.proposal.return.schedule.satReturnTime,
        this.proposal.return.schedule.sunReturnTime
      ].filter(Boolean) : []
    }
  },
  computed: {
    hasReturn () {
      return !this.proposal.outward.oneWay;
    },
    isRegular () {
      return this.proposal.outward.frequency === 2;
    },
    hasMonday () {
      return (this.proposal.outward && this.proposal.outward.schedule.mon) || 
        (this.proposal.return && this.proposal.return.schedule.mon);
    },
    hasTuesday () {
      return (this.proposal.outward && this.proposal.outward.schedule.tue) || 
        (this.proposal.return && this.proposal.return.schedule.tue);
    },
    hasWednesday () {
      return (this.proposal.outward && this.proposal.outward.schedule.wed) || 
        (this.proposal.return && this.proposal.return.schedule.wed);
    },
    hasThursday () {
      return (this.proposal.outward && this.proposal.outward.schedule.thu) || 
        (this.proposal.return && this.proposal.return.schedule.thu);
    },
    hasFriday () {
      return (this.proposal.outward && this.proposal.outward.schedule.fri) || 
        (this.proposal.return && this.proposal.return.schedule.fri);
    },
    hasSaturday () {
      return (this.proposal.outward && this.proposal.outward.schedule.sat) || 
        (this.proposal.return && this.proposal.return.schedule.sat);
    },
    hasSunday () {
      return (this.proposal.outward && this.proposal.outward.schedule.sun) || 
        (this.proposal.return && this.proposal.return.schedule.sun);
    }
  }
}
</script>

<style scoped>

</style>
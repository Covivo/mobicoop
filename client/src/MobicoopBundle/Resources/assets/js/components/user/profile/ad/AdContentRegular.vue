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
          :date-end-of-validity="ad.outward.outwardLimitDate"
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
                :origin="ad.outward.outwardWaypoints[0].address"
                :destination="ad.outward.outwardWaypoints[ad.outward.outwardWaypoints.length - 1].address"
                :type="ad.outward.frequency"
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
import Schedules from '@components/user/profile/ad/Schedules.vue';

export default {
  components: {
    RegularDaysSummary,
    RouteSummary,
    Schedules
  },
  props: {
    ad: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      outwardTimes: this.ad.outward ? [
        this.ad.outward.schedule.monOutwardTime, 
        this.ad.outward.schedule.tueOutwardTime, 
        this.ad.outward.schedule.wedOutwardTime, 
        this.ad.outward.schedule.thuOutwardTime, 
        this.ad.outward.schedule.friOutwardTime, 
        this.ad.outward.schedule.satOutwardTime, 
        this.ad.outward.schedule.sunOutwardTime
      ].filter(Boolean) : [],
      returnTimes: this.ad.return ? [
        this.ad.return.schedule.monReturnTime,
        this.ad.return.schedule.tueReturnTime,
        this.ad.return.schedule.wedReturnTime,
        this.ad.return.schedule.thuReturnTime,
        this.ad.return.schedule.friReturnTime,
        this.ad.return.schedule.satReturnTime,
        this.ad.return.schedule.sunReturnTime
      ].filter(Boolean) : []
    }
  },
  computed: {
    hasReturn () {
      return !this.ad.outward.oneWay;
    },
    isRegular () {
      return this.ad.outward.frequency === 2;
    },
    hasMonday () {
      return (this.ad.outward && this.ad.outward.schedule.mon) || 
        (this.ad.return && this.ad.return.schedule.mon);
    },
    hasTuesday () {
      return (this.ad.outward && this.ad.outward.schedule.tue) || 
        (this.ad.return && this.ad.return.schedule.tue);
    },
    hasWednesday () {
      return (this.ad.outward && this.ad.outward.schedule.wed) || 
        (this.ad.return && this.ad.return.schedule.wed);
    },
    hasThursday () {
      return (this.ad.outward && this.ad.outward.schedule.thu) || 
        (this.ad.return && this.ad.return.schedule.thu);
    },
    hasFriday () {
      return (this.ad.outward && this.ad.outward.schedule.fri) || 
        (this.ad.return && this.ad.return.schedule.fri);
    },
    hasSaturday () {
      return (this.ad.outward && this.ad.outward.schedule.sat) || 
        (this.ad.return && this.ad.return.schedule.sat);
    },
    hasSunday () {
      return (this.ad.outward && this.ad.outward.schedule.sun) || 
        (this.ad.return && this.ad.return.schedule.sun);
    }
  }
}
</script>

<style scoped>

</style>
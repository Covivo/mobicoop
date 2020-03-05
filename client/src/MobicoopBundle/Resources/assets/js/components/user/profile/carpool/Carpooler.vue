<template>
  <v-container>
    <v-row>
      <v-col>
        <route-summary
          :origin="ad.outwardWaypoints[0].address"
          :destination="ad.outwardWaypoints[ad.outwardWaypoints.length - 1].address"
          :type="ad.frequency"
          :regular="isRegular"
          text-color-class="primary--text text--darken-2"
          icon-color="accent"
        />
      </v-col>
    </v-row>
    <!--    <v-row>-->
    <!--      <v-col cols="5">-->
    <!--        <regular-days-summary-->
    <!--          :mon-active="hasMonday"-->
    <!--          :tue-active="hasTuesday"-->
    <!--          :wed-active="hasWednesday"-->
    <!--          :thu-active="hasThursday"-->
    <!--          :fri-active="hasFriday"-->
    <!--          :sat-active="hasSaturday"-->
    <!--          :sun-active="hasSunday"-->
    <!--          :date-end-of-validity="ad.outwardLimitDate"-->
    <!--        />-->
    <!--      </v-col>-->

    <!--      <v-col class="py-0">-->
    <!--        <schedules-->
    <!--          :outward-times="outwardTimes"-->
    <!--          :return-times="returnTimes"-->
    <!--          :is-return="hasReturn"-->
    <!--          :is-regular="isRegular"-->
    <!--        />-->
    <!--      </v-col>-->
    <!--    </v-row>-->
    <v-row>
      <v-col
        cols="8"
      >
        <carpooler-identity
          :carpooler="result.carpooler"
        />
      </v-col>
      <v-col cols="4">
        <carpooler-contact
          :carpooler="result.carpooler"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
// import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
import CarpoolerIdentity from "@components/carpool/utilities/CarpoolerIdentity";
import CarpoolerContact from "@components/carpool/utilities/CarpoolerContact";
// import Schedules from '@components/user/profile/ad/Schedules.vue';

export default {
  components: {
    CarpoolerIdentity,
    CarpoolerContact,
    // RegularDaysSummary,
    RouteSummary,
    // Schedules
  },
  props: {
    ad: {
      type: Object,
      required: true
    },
    result: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
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
      return this.ad && this.ad.schedule.mon;
    },
    hasTuesday () {
      return this.ad && this.ad.schedule.tue;
    },
    hasWednesday () {
      return this.ad && this.ad.schedule.wed;
    },
    hasThursday () {
      return this.ad && this.ad.schedule.thu;
    },
    hasFriday () {
      return this.ad && this.ad.schedule.fri;
    },
    hasSaturday () {
      return this.ad && this.ad.schedule.sat;
    },
    hasSunday () {
      return this.ad && this.ad.schedule.sun;
    }
  },
}
</script>

<style scoped>

</style>
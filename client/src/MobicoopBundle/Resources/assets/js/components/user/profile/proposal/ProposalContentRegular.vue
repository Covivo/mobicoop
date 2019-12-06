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
          :date-end-of-validity="proposal.outward.criteria.toDate"
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
              cols="6"
              class="py-0"
            >
              <route-summary
                :origin="proposal.outward.waypoints[0].address"
                :destination="proposal.outward.waypoints[proposal.outward.waypoints.length - 1].address"
                :type="proposal.outward.criteria.frequency"
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
        this.proposal.outward.criteria.monTime, 
        this.proposal.outward.criteria.tueTime, 
        this.proposal.outward.criteria.wedTime, 
        this.proposal.outward.criteria.thuTime, 
        this.proposal.outward.criteria.friTime, 
        this.proposal.outward.criteria.satTime, 
        this.proposal.outward.criteria.sunTime
      ].filter(Boolean) : [],
      returnTimes: this.proposal.return ? [
        this.proposal.return.criteria.monTime,
        this.proposal.return.criteria.tueTime,
        this.proposal.return.criteria.wedTime,
        this.proposal.return.criteria.thuTime,
        this.proposal.return.criteria.friTime,
        this.proposal.return.criteria.satTime,
        this.proposal.return.criteria.sunTime
      ].filter(Boolean) : []
    }
  },
  computed: {
    hasReturn () {
      return !isEmpty(this.proposal.return);
    },
    isRegular () {
      return this.proposal.outward.criteria.frequency === 2;
    },
    hasMonday () {
      return (this.proposal.outward && this.proposal.outward.criteria.monCheck) || 
        (this.proposal.return && this.proposal.return.criteria.monCheck);
    },
    hasTuesday () {
      return (this.proposal.outward && this.proposal.outward.criteria.tueCheck) || 
        (this.proposal.return && this.proposal.return.criteria.tueCheck);
    },
    hasWednesday () {
      return (this.proposal.outward && this.proposal.outward.criteria.wedCheck) || 
        (this.proposal.return && this.proposal.return.criteria.wedCheck);
    },
    hasThursday () {
      return (this.proposal.outward && this.proposal.outward.criteria.thuCheck) || 
        (this.proposal.return && this.proposal.return.criteria.thuCheck);
    },
    hasFriday () {
      return (this.proposal.outward && this.proposal.outward.criteria.friCheck) || 
        (this.proposal.return && this.proposal.return.criteria.friCheck);
    },
    hasSaturday () {
      return (this.proposal.outward && this.proposal.outward.criteria.satCheck) || 
        (this.proposal.return && this.proposal.return.criteria.satCheck);
    },
    hasSunday () {
      return (this.proposal.outward && this.proposal.outward.criteria.sunCheck) || 
        (this.proposal.return && this.proposal.return.criteria.sunCheck);
    }
  }
}
</script>

<style scoped>

</style>
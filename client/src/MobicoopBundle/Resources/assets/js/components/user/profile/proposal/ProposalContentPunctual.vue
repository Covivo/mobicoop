<template>
  <v-container>
    <v-row v-if="hasOutward">
      <schedules
        date-time-format="ui.i18n.date.format.shortDate"
        :outward-times="[proposal.outward.criteria.fromDate]"
      />
    </v-row>
    <v-row
      v-if="hasOutward"
    >
      <v-col
        cols="6"
        class="pa-0"
      >
        <route-summary
          :origin="origin"
          :destination="destination"
          :type="frequency"
          :time="proposal.outward.criteria.fromTime"
          text-color-class="primary--text"
          icon-color="accent"
        />
      </v-col>
    </v-row>
    <v-row v-if="hasReturn">
      <schedules
        :is-return="true"
        :is-outward="false"
        date-time-format="ui.i18n.date.format.shortDate"
        :return-times="[proposal.return.criteria.fromDate]"
      />
    </v-row>
    <v-row v-if="hasReturn">
      <v-col
        cols="6"
        class="pa-0"
      >
        <route-summary
          :origin="destination"
          :destination="origin"
          :type="frequency"
          :time="proposal.return.criteria.fromTime"
          text-color-class="primary--text"
          icon-color="accent"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {isEmpty} from "lodash";
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
import Schedules from '@components/user/profile/proposal/Schedules.vue';

export default {
  components: {
    RouteSummary,
    Schedules
  },
  props: {
    proposal: {
      type: Object,
      required: true
    }
  },
  computed: {
    hasOutward () {
      return !isEmpty(this.proposal.outward);
    },
    hasReturn () {
      return !isEmpty(this.proposal.return);
    },
    frequency () {
      return this.hasOutward ? this.proposal.outward.criteria.frequency : 
        this.hasReturn ? this.proposal.return.criteria.frequency : null;
    },
    origin () {
      return this.hasOutward ? this.proposal.outward.waypoints[0].address : 
        this.hasReturn ? this.proposal.return.waypoints[0].address : null;
    },
    destination () {
      return this.hasOutward ? this.proposal.outward.waypoints[this.proposal.outward.waypoints.length - 1].address :
        this.hasReturn ? this.proposal.return.waypoints[this.proposal.outward.waypoints.length - 1].address : null;
    }
  }
}
</script>

<style scoped>

</style>
<template>
  <v-container>
    <v-row v-if="hasOutward">
      <schedules
        date-time-format="ui.i18n.date.format.shortDate"
        :outward-times="[proposal.outward.outwardDate]"
      />
    </v-row>
    <v-row
      v-if="hasOutward"
    >
      <v-col
        class="pa-0"
      >
        <route-summary
          :origin="origin"
          :destination="destination"
          :type="frequency"
          :time="proposal.outward.outwardTime"
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
        :return-times="[proposal.return.returnDate]"
      />
    </v-row>
    <v-row v-if="hasReturn">
      <v-col
        class="pa-0"
      >
        <route-summary
          :origin="destination"
          :destination="origin"
          :type="frequency"
          :time="proposal.return.returnTime"
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
      return !this.proposal.outward.oneWay;
    },
    frequency () {
      return this.hasOutward ? this.proposal.outward.frequency : 
        this.hasReturn ? this.proposal.return.frequency : null;
    },
    origin () {
      return this.hasOutward ? this.proposal.outward.outwardWaypoints[0].address : 
        this.hasReturn ? this.proposal.return.returnWaypoints[0].address : null;
    },
    destination () {
      return this.hasOutward ? this.proposal.outward.outwardWaypoints[this.proposal.outward.outwardWaypoints.length - 1].address :
        this.hasReturn ? this.proposal.return.returnWaypoints[this.proposal.return.returnWaypoints.length - 1].address : null;
    }
  }
}
</script>

<style scoped>

</style>
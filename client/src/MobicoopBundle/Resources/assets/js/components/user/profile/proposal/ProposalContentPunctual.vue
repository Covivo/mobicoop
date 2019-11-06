<template>
  <v-container>
    <v-row>
      <schedules
        date-time-format="ui.i18n.date.format.shortDate"
        :outward-times="[proposal.outward.criteria.fromDate]"
      />
    </v-row>
    <v-row>
      <v-col
        cols="6"
        class="pa-0"
      >
        <route-summary
          :origin="proposal.outward.waypoints[0].address"
          :destination="proposal.outward.waypoints[proposal.outward.waypoints.length - 1].address"
          :type="proposal.outward.criteria.frequency"
          :time="proposal.outward.criteria.fromTime"
          text-color-class="primary--text text--darken-3"
          icon-color="accent"
        />
      </v-col>
    </v-row>
    <v-row>
      <schedules
        :is-return="true"
        :is-outward="false"
        date-time-format="ui.i18n.date.format.shortDate"
        :return-times="[proposal.return.criteria.fromDate]"
      />
    </v-row>
    <v-row>
      <v-col
        cols="6"
        class="pa-0"
      >
        <route-summary
          :origin="proposal.outward.waypoints[proposal.outward.waypoints.length - 1].address"
          :destination="proposal.outward.waypoints[0].address"
          :type="proposal.outward.criteria.frequency"
          :time="proposal.return.criteria.fromTime"
          text-color-class="primary--text text--darken-3"
          icon-color="accent"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
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
  }
}
</script>

<style scoped>

</style>
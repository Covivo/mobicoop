<template>
  <v-container>
    <v-row>
      <schedules :show-return="false" />
    </v-row>
    <v-row>
      <route-summary
        :origin="proposal.outward.waypoints[0].address"
        :destination="proposal.outward.waypoints[proposal.outward.waypoints.length - 1].address"
        :type="proposal.outward.criteria.frequency"
        text-color-class="primary--text text--darken-3"
        icon-color="accent"
      />
    </v-row>
    Ponctuel
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
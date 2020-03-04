<template>
  <v-container>
    <v-row>
      <schedules
        date-time-format="ui.i18n.date.format.shortDate"
        :outward-times="[ad.outwardDate]"
      />
    </v-row>
    <v-row>
      <v-col
        class="pa-0"
      >
        <route-summary
          :origin="origin"
          :destination="destination"
          :type="frequency"
          :time="ad.outwardTime"
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
        :return-times="[ad.returnDate]"
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
          :time="ad.returnTime"
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
import Schedules from '@components/user/profile/ad/Schedules.vue';

export default {
  components: {
    RouteSummary,
    Schedules
  },
  props: {
    ad: {
      type: Object,
      required: true
    }
  },
  computed: {
    hasReturn () {
      return !this.ad.oneWay;
    },
    frequency () {
      return this.ad.frequency;
    },
    origin () {
      return this.ad.outwardWaypoints[0].address;
    },
    destination () {
      return this.ad.outwardWaypoints[this.ad.outwardWaypoints.length - 1].address;
    }
  }
}
</script>

<style scoped>

</style>
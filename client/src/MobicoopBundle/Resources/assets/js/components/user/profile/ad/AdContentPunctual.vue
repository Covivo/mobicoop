<template>
  <v-container>
    <v-row v-if="hasOutward">
      <schedules
        date-time-format="ui.i18n.date.format.shortDate"
        :outward-times="[ad.outward.outwardDate]"
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
          :time="ad.outward.outwardTime"
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
        :return-times="[ad.return.returnDate]"
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
          :time="ad.return.returnTime"
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
    hasOutward () {
      return !isEmpty(this.ad.outward);
    },
    hasReturn () {
      return !this.ad.outward.oneWay;
    },
    frequency () {
      return this.hasOutward ? this.ad.outward.frequency : 
        this.hasReturn ? this.ad.return.frequency : null;
    },
    origin () {
      return this.hasOutward ? this.ad.outward.outwardWaypoints[0].address : 
        this.hasReturn ? this.ad.return.returnWaypoints[0].address : null;
    },
    destination () {
      return this.hasOutward ? this.ad.outward.outwardWaypoints[this.ad.outward.outwardWaypoints.length - 1].address :
        this.hasReturn ? this.ad.return.returnWaypoints[this.ad.return.returnWaypoints.length - 1].address : null;
    }
  }
}
</script>

<style scoped>

</style>
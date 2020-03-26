<template>
  <v-container class="py-0">
    <v-row>
      <schedules
        date-time-format="ui.i18n.date.format.shortDate"
        :outward-time="ad.outwardDate"
        :is-refined="isRefined"
      />
    </v-row>
    <v-row>
      <v-col
        class="pa-0"
      >
        <route-summary
          :origin="origin"
          :destination="destination"
          :type="ad.frequency"
          :time="ad.outwardTime"
          :compact="true"
          text-color-class="primary--text"
          icon-color="accent"
        />
      </v-col>
    </v-row>
    <v-row v-if="hasReturn && !isRefined">
      <schedules
        :is-return="true"
        :is-outward="false"
        date-time-format="ui.i18n.date.format.shortDate"
        :return-times="[ad.returnDate]"
      />
    </v-row>
    <v-row v-if="hasReturn && !isRefined">
      <v-col
        class="pa-0"
      >
        <route-summary
          :origin="destination"
          :destination="origin"
          :type="ad.frequency"
          :compact="true"
          :time="ad.returnTime"
          text-color-class="primary--text"
          icon-color="accent"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
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
    },
    // if we want more refined display of data
    isRefined: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    hasReturn () {
      return !this.ad.oneWay;
    },
    origin () {
      return this.ad.outwardWaypoints.find(el => el.position === 0)["address"];
    },
    destination () {
      return this.ad.outwardWaypoints.find(el => el.destination === true)["address"];
    }
  }
}
</script>

<style scoped>

</style>
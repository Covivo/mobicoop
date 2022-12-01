<template>
  <v-container class="py-0">
    <v-row>
      <schedules
        :is-return="ad.type == 2"
        :is-outward="ad.type == 0 || ad.type == 1"
        date-time-format="shortDate"
        :outward-time="isCarpool ? (ad.driver.fromDate ? ad.driver.fromDate : ad.passengers[0].fromDate) : ad.outwardDate"
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
          :time="isCarpool ? (ad.driver.pickUpTime ? ad.driver.pickUpTime : ad.outwardTime) : ad.outwardTime"
          :compact="true"
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
    },
    isCarpool: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    hasReturn () {
      return this.ad.returnDate !== null;
    },
    origin () {
      return {
        streetAddress: this.ad.waypoints.find(el => el.position === 0)['streetAddress'],
        addressLocality: this.ad.waypoints.find(el => el.position === 0)['addressLocality'],
        name: this.ad.waypoints.find(el => el.position === 0)['name'],
      }
    },
    destination () {
      return {
        streetAddress: this.ad.waypoints.find(el => el.destination === true)['streetAddress'],
        addressLocality: this.ad.waypoints.find(el => el.destination === true)['addressLocality'],
        name: this.ad.waypoints.find(el => el.destination === true)['name']
      }
    }
  }
}
</script>

<style scoped>

</style>
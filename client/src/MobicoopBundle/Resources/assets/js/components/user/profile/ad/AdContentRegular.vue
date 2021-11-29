<template>
  <v-container
    fluid
    class="pa-0"
  >
    <v-row>
      <v-col cols="6">
        <regular-days-summary
          v-if="!isCarpool || (isCarpool && ad.passengers.length>0)"
          :mon-active="ad.schedule.mon.check"
          :tue-active="ad.schedule.tue.check"
          :wed-active="ad.schedule.wed.check"
          :thu-active="ad.schedule.thu.check"
          :fri-active="ad.schedule.fri.check"
          :sat-active="ad.schedule.sat.check"
          :sun-active="ad.schedule.sun.check"
          :date-end-of-validity="ad.toDate"
        />
        <regular-days-summary
          v-else
          :mon-active="ad.driver.schedule.mon.check"
          :tue-active="ad.driver.schedule.tue.check"
          :wed-active="ad.driver.schedule.wed.check"
          :thu-active="ad.driver.schedule.thu.check"
          :fri-active="ad.driver.schedule.fri.check"
          :sat-active="ad.driver.schedule.sat.check"
          :sun-active="ad.driver.schedule.sun.check"
          :date-end-of-validity="ad.toDate"
        />
      </v-col>
      
      <v-col class="py-0">
        <schedules
          v-if="!isCarpool || (isCarpool && ad.passengers.length>0)"
          :multiple-outward="ad.schedule.outwardTime=='multiple'"
          :outward-time="ad.schedule.outwardTime"
          :multiple-return="ad.schedule.returnTime=='multiple'"
          :return-time="ad.schedule.returnTime"
          :is-return="ad.returnFromDate !== null"
          :is-regular="ad.frequency == 2"
          :has-days="true"
        />
        <schedules
          v-else
          :multiple-outward="ad.driver.schedule.pickUpTime=='multiple'"
          :outward-time="ad.driver.schedule.pickUpTime"
          :multiple-return="ad.driver.schedule.returnPickUpTime=='multiple'"
          :return-time="ad.driver.schedule.returnPickUpTime"
          :is-return="ad.driver.returnFromDate !== null"
          :is-regular="ad.frequency == 2"
          :has-days="true"
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
              class="py-0"
            >
              <route-summary
                :compact="true"
                :origin="origin"
                :destination="destination"
                :type="ad.frequency"
                :regular="ad.frequency == 2"
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
import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
import Schedules from '@components/user/profile/ad/Schedules.vue';

export default {
  components: {
    RegularDaysSummary,
    RouteSummary,
    Schedules
  },
  props: {
    ad: {
      type: Object,
      required: true
    },
    isCarpool: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
    }
  },
  computed: {
    origin () {
      return {
        streetAddress: this.ad.waypoints.find(el => el.position === 0)['streetAddress'],
        addressLocality: this.ad.waypoints.find(el => el.position === 0)['addressLocality'],
        name: this.ad.waypoints.find(el => el.position === 0)['name']
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
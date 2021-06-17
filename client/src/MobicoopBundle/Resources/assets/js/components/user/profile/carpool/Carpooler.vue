<template>
  <v-container>
    <v-row justify="center">
      <v-col
        class="pa-0"
      >
        <v-container :class="frequency == 2 ? 'primary lighten-5 py-0' : 'py-0'">
          <v-row class="py-0">
            <v-col
              class="py-0"
            >
              <route-summary
                :origin="origin"
                :destination="destination"
                :type="frequency"
                :time="frequency == 1 ? (driver ? carpooler.startTime : carpooler.pickUpTime) : null"
                :regular="frequency==2"
                :compact="true"
                text-color-class="primary--text"
                icon-color="accent"
              />
            </v-col>
          </v-row>
        </v-container>
      </v-col>
    </v-row>
    <v-row
      v-if="frequency == 2"
      align="center"
    >
      <v-col
        v-for="(schedule, index) in carpooler.schedules"
        :key="index"
        cols="12"
        class="pb-0 px-0"
      >
        <v-row
          no-gutters
          align="start"
        >
          <v-col
            cols="5"
            class="pa-0"
          >
            <regular-days-summary
              :mon-active="schedule.mon"
              :tue-active="schedule.tue"
              :wed-active="schedule.wed"
              :thu-active="schedule.thu"
              :fri-active="schedule.fri"
              :sat-active="schedule.sat"
              :sun-active="schedule.sun"
              :date-start-of-validity="carpooler.fromDate"
              :date-end-of-validity="carpooler.toDate"
            />
          </v-col>

          <v-col
            class="pa-0"
          >
            <schedules
              :outward-time="driver ? schedule.startTime : schedule.pickUpTime"
              :return-time="driver ? schedule.returnStartTime : schedule.returnPickUpTime"
              :is-regular="frequency == 2"
              :no-gutters="true"
              :has-days="true"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row
      no-gutters
      align="center"
    >
      <v-col
        :cols="carpooler.payment.status !== null ? 4 : 7"
        :class="{'ml-n11': $vuetify.breakpoint.lgAndDown}"
      >
        <carpooler-identity
          :carpooler="carpooler"
        />
      </v-col>
      <v-col
        cols="3"
      >
        <!--display phone is always true when ask is accepted-->
        <carpooler-contact
          :carpooler="carpooler"
          :ask-id="carpooler.askId"
          :display-mail-box="true"
          :user="user"
        />
      </v-col>
      <v-col
        v-if="passenger"
        cols="2"
        class="font-weight-bold primary--text text-h5 text-right"
        :class="{'text-h6 ml-n6': $vuetify.breakpoint.mdAndDown}"
      >
        {{ carpooler.price }}€
      </v-col>
      <v-col
        v-if="carpooler.payment.itemId !== null || carpooler.payment.status == 4"
        :cols="passenger ? 3 : 5"
        class="text-right"
      >
        <ad-payment
          :is-driver="!driver"
          :payment-status="carpooler.payment.status"
          :frequency="frequency"
          :payment-item-id="carpooler.payment.itemId"
          :payment-electronic-active="paymentElectronicActive"
        />        
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/carpool/CarpoolFooter/";
import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
import CarpoolerIdentity from "@components/carpool/utilities/CarpoolerIdentity";
import CarpoolerContact from "@components/carpool/utilities/CarpoolerContact";
import Schedules from '@components/user/profile/ad/Schedules.vue';
import AdPayment from '@components/user/profile/ad/AdPayment.vue';

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    CarpoolerIdentity,
    CarpoolerContact,
    RegularDaysSummary,
    RouteSummary,
    Schedules,
    AdPayment
  },
  props: {
    carpooler: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      default: null
    },
    driver: {
      type: Boolean
    },
    passenger: {
      type: Boolean
    },
    frequency: {
      type: Number,
      default: 1
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
  },
  data () {
    return {
      
    }
  },
  computed: {
    origin () {
      return {
        streetAddress: this.carpooler.waypoints.find(el => el.origin === true)['streetAddress'],
        addressLocality: this.carpooler.waypoints.find(el => el.origin === true)['addressLocality']
      }
    },
    destination () {
      return {
        streetAddress: this.carpooler.waypoints.find(el => el.destination === true)['streetAddress'],
        addressLocality: this.carpooler.waypoints.find(el => el.destination === true)['addressLocality']
      }
    }
  }
}
</script>

<style scoped>

</style>
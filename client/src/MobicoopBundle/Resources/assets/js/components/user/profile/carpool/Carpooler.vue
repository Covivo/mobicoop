<template>
  <v-container>
    <v-row justify="center">
      <v-col
        class="pa-0"
      >
        <v-container :class=" isRegular ? 'primary lighten-5 py-0' : 'py-0'">
          <v-row class="py-0">
            <v-col
              class="py-0"
            >
              <route-summary
                :origin="this.ad.role===2?carpoolInfos.outward.originPassenger:carpoolInfos.outward.originDriver"
                :destination="this.ad.role===2?carpoolInfos.outward.destinationPassenger:carpoolInfos.outward.destinationDriver"
                :type="result.frequency"
                :time="!isRegular ? result.time : null"
                :regular="isRegular"
                :compact="true"
                text-color-class="primary--text"
                icon-color="accent"
              />
            </v-col>
          </v-row>
        </v-container>
      </v-col>
    </v-row>
    <v-row v-if="isRegular">
      <v-col cols="5">
        <regular-days-summary
          :mon-active="hasMonday"
          :tue-active="hasTuesday"
          :wed-active="hasWednesday"
          :thu-active="hasThursday"
          :fri-active="hasFriday"
          :sat-active="hasSaturday"
          :sun-active="hasSunday"
          :date-start-of-validity="result.fromDate"
          :date-end-of-validity="result.toDate"
        />
      </v-col>

      <v-col class="py-0">
        <schedules
          :outward-times="outwardTimes"
          :return-times="returnTimes"
          :is-return="hasReturn"
          :is-regular="isRegular"
        />
      </v-col>
    </v-row>
    <v-row
      no-gutters
      align="center"
    >
      <v-col
        cols="7"
      >
        <carpooler-identity
          :carpooler="result.carpooler"
        />
      </v-col>
      <v-col
        cols="3"
      >
        <!--display phone is always true when ask is accepted-->
        <carpooler-contact
          :carpooler="result.carpooler"
          :ask-id="ad.askId"
          :display-phone="true"
          :display-mail-box="true"
          :user="user"
        />
      </v-col>
      <!--      <v-col-->
      <!--        cols="2"-->
      <!--        class="text-right"-->
      <!--      >-->
      <!--        <v-btn-->
      <!--          rounded-->
      <!--          depressed-->
      <!--          color="secondary"-->
      <!--          class="text-none"-->
      <!--          height="40px"-->
      <!--        >-->
      <!--          {{ $t('ui.button.cancel') }}-->
      <!--        </v-btn>-->
      <!--      </v-col>-->
      <v-col
        v-if="isDriver"
        cols="2"
        class="font-weight-bold primary--text headline text-right"
      >
        {{ result.roundedPrice }}€
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Translations from "@translations/components/user/profile/carpool/CarpoolFooter.js";

import RegularDaysSummary from '@components/carpool/utilities/RegularDaysSummary.vue';
import RouteSummary from '@components/carpool/utilities/RouteSummary.vue';
import CarpoolerIdentity from "@components/carpool/utilities/CarpoolerIdentity";
import CarpoolerContact from "@components/carpool/utilities/CarpoolerContact";
import Schedules from '@components/user/profile/ad/Schedules.vue';

export default {
  i18n: {
    messages: Translations
  },
  components: {
    CarpoolerIdentity,
    CarpoolerContact,
    RegularDaysSummary,
    RouteSummary,
    Schedules
  },
  props: {
    ad: {
      type: Object,
      required: true
    },
    result: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      carpoolInfos: null,
      outwardTimes:[],
      returnTimes:[],
      hasReturn: null,
      isRegular: null,
      hasMonday: null,
      hasTuesday: null,
      hasWednesday: null,
      hasThursday: null,
      hasFriday: null,
      hasSaturday: null,
      hasSunday: null
    }
  },
  computed: {
    isDriver() {
      return this.ad.role === 1 || this.ad.role === 3
    },
    isPassenger() {
      return this.ad.role === 2 || this.ad.role === 3
    }
  },
  mounted() {
    this.setCarpoolInfo();
  },
  methods: {
    setCarpoolInfo() {
      this.carpoolInfos= this.ad.role === 2 ? this.result.resultPassenger : this.result.resultDriver;
      this.outwardTimes.push(
        this.carpoolInfos.outward.monTime,
        this.carpoolInfos.outward.tueTime,
        this.carpoolInfos.outward.wedTime,
        this.carpoolInfos.outward.thuTime,
        this.carpoolInfos.outward.friTime,
        this.carpoolInfos.outward.satTime,
        this.carpoolInfos.outward.sunTime
      );
      this.returnTimes.push(
        this.carpoolInfos.return.monTime,
        this.carpoolInfos.return.tueTime,
        this.carpoolInfos.return.wedTime,
        this.carpoolInfos.return.thuTime,
        this.carpoolInfos.return.friTime,
        this.carpoolInfos.return.satTime,
        this.carpoolInfos.return.sunTime
      );
      this.hasReturn = this.carpoolInfos.return?true:false;
      this.isRegular = this.result.frequency === 2;
      this.hasMonday  = this.carpoolInfos.outward.monCheck;
      this.hasTuesday = this.carpoolInfos.outward.tueCheck;
      this.hasWednesday = this.carpoolInfos.outward.wedCheck;
      this.hasThursday = this.carpoolInfos.outward.thuCheck;
      this.hasFriday = this.carpoolInfos.outward.friCheck;
      this.hasSaturday = this.carpoolInfos.outward.satCheck;
      this.hasSunday = this.carpoolInfos.outward.sunCheck;
    }
  },
}
</script>

<style scoped>

</style>
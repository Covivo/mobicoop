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
                :origin="ask.role === 2 ? carpoolInfos.outward.originPassenger : carpoolInfos.outward.originDriver"
                :destination="ask.role === 2 ? carpoolInfos.outward.destinationPassenger : carpoolInfos.outward.destinationDriver"
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
    <v-row
      v-if="isRegular"
      align="center"
    >
      <v-col
        v-for="(schedule, index) in schedules"
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
              :date-start-of-validity="index === schedules.length -1 ? carpoolInfos.outward.fromDate : null"
              :date-end-of-validity="index === schedules.length -1 ? carpoolInfos.outward.toDate : null"
            />
          </v-col>

          <v-col
            class="pa-0"
          >
            <schedules
              :outward-time="schedule.outwardTime"
              :return-time="schedule.returnTime"
              :is-return="scheduleHasReturn(schedule)"
              :is-outward="scheduleHasOutward(schedule)"
              :is-regular="isRegular"
              :no-gutters="true"
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
          :ask-id="ask.askId"
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
        v-if="isPassenger"
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
    ask: {
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
      hasSunday: null,
      arrayDay: ["mon", "tue", "wed", "thu", "fri", "sat", "sun"],
      schedules: []
    }
  },
  computed: {
    isDriver() {
      return this.ask.role === 1 || this.ask.role === 3
    },
    isPassenger() {
      return this.ask.role === 2 || this.ask.role === 3
    }
  },
  created() {
    this.setCarpoolInfo();
  },
  methods: {
    setCarpoolInfo() {
      this.carpoolInfos = this.ask.role === 2 ? this.result.resultPassenger : this.result.resultDriver;
      if (this.carpoolInfos !== null) {
        this.outwardTimes.push(
          this.carpoolInfos.outward.monTime,
          this.carpoolInfos.outward.tueTime,
          this.carpoolInfos.outward.wedTime,
          this.carpoolInfos.outward.thuTime,
          this.carpoolInfos.outward.friTime,
          this.carpoolInfos.outward.satTime,
          this.carpoolInfos.outward.sunTime
        );
        if (this.carpoolInfos.return !== null) {
          this.returnTimes.push(
            this.carpoolInfos.return.monTime,
            this.carpoolInfos.return.tueTime,
            this.carpoolInfos.return.wedTime,
            this.carpoolInfos.return.thuTime,
            this.carpoolInfos.return.friTime,
            this.carpoolInfos.return.satTime,
            this.carpoolInfos.return.sunTime
          );
        }

        this.hasReturn = !!this.carpoolInfos.return;
        this.hasMonday = this.carpoolInfos.outward.monCheck || this.carpoolInfos.return && this.carpoolInfos.return.monCheck;
        this.hasTuesday = this.carpoolInfos.outward.tueCheck || this.carpoolInfos.return && this.carpoolInfos.return.tueCheck;
        this.hasWednesday = this.carpoolInfos.outward.wedCheck || this.carpoolInfos.return && this.carpoolInfos.return.wedCheck;
        this.hasThursday = this.carpoolInfos.outward.thuCheck || this.carpoolInfos.return && this.carpoolInfos.return.thuCheck;
        this.hasFriday = this.carpoolInfos.outward.friCheck || this.carpoolInfos.return && this.carpoolInfos.return.friCheck;
        this.hasSaturday = this.carpoolInfos.outward.satCheck || this.carpoolInfos.return && this.carpoolInfos.return.satCheck;
        this.hasSunday = this.carpoolInfos.outward.sunCheck || this.carpoolInfos.return && this.carpoolInfos.return.sunCheck;

        // let schedule = this.initSchedule;
        let tempSchedules = [];

        this.arrayDay.forEach(day => {
          if (this.carpoolInfos.outward[day + "Check"] === true || this.carpoolInfos.return && this.carpoolInfos.return[day + "Check"] === true) {
            tempSchedules.push({
              day: day,
              outwardTime: this.carpoolInfos.outward && this.carpoolInfos.outward[day + 'Check'] ? this.carpoolInfos.outward[day + 'Time'] : null,
              returnTime: this.carpoolInfos.return && this.carpoolInfos.return[day + 'Check'] ? this.carpoolInfos.return[day + 'Time'] : null
            });
          }
        });

        let schedulesLength = tempSchedules.length;
        let daysDone = [];
        for (let i = 0; i < schedulesLength; i++) {
          if (tempSchedules.length === 0) break;
          if (daysDone.includes(tempSchedules[i].day)) continue;
          let days = tempSchedules.filter(elem => {return elem.outwardTime === tempSchedules[i].outwardTime && elem.returnTime === tempSchedules[i].returnTime});

          this.schedules.push({
            mon: days.some(day => {return day.day === 'mon'}),
            tue: days.some(day => {return day.day === 'tue'}),
            wed: days.some(day => {return day.day === 'wed'}),
            thu: days.some(day => {return day.day === 'thu'}),
            fri: days.some(day => {return day.day === 'fri'}),
            sat: days.some(day => {return day.day === 'sat'}),
            sun: days.some(day => {return day.day === 'sun'}),
            outwardTime: tempSchedules[i].outwardTime,
            returnTime: tempSchedules[i].returnTime
          });

          days.forEach(day => {
            daysDone.push(day.day)
          })
        }
      }

      this.isRegular = this.result.frequency === 2;
    },
    scheduleHasReturn (schedule) {
      return schedule.returnTime !== null;
    },
    scheduleHasOutward (schedule) {
      return schedule.outwardTime !== null;
    }
  }
}
</script>

<style scoped>

</style>
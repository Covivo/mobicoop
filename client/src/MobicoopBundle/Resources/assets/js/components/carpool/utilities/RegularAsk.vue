<template>
  <div>
    <v-container>
      <!-- Direction -->
      <v-row
        dense
        align="center"
      >
        <v-col
          cols="2"
          class="text-h6"
        >
          {{ type == 1 ? $t('outward') : $t('return') }}
        </v-col>
        <v-col
          v-if="originDriver"
          cols="auto"
        >
          <v-chip>
            {{ originDriver.addressLocality }}
          </v-chip>
        </v-col>
        <v-col
          v-if="originDriver"
          cols="auto"
        >
          <v-icon
            slot="prepend"
          >
            mdi-arrow-right
          </v-icon>
        </v-col>
        <v-col
          v-if="(!originDriver && originPassenger) || (originPassenger && originPassenger.addressLocality != originDriver.addressLocality)"
          cols="auto"
        >
          <v-chip>
            {{ originPassenger.addressLocality }}
          </v-chip>
        </v-col>
        <v-col
          v-if="(!originDriver && originPassenger) || (originPassenger && originPassenger.addressLocality != originDriver.addressLocality)"
          cols="auto"
        >
          <v-icon
            slot="prepend"
          >
            mdi-arrow-right
          </v-icon>
        </v-col>
        <v-col
          v-if="destinationPassenger"
          cols="auto"
        >
          <v-chip>
            {{ destinationPassenger.addressLocality }}
          </v-chip>
        </v-col>
        <v-col
          v-if="(!destinationPassenger && destinationDriver) || (destinationDriver && destinationPassenger.addressLocality != destinationDriver.addressLocality)"
          cols="auto"
        >
          <v-icon
            slot="prepend"
          >
            mdi-arrow-right
          </v-icon>
        </v-col>
        <v-col
          v-if="(!destinationPassenger && destinationDriver) || (destinationDriver && destinationPassenger.addressLocality != destinationDriver.addressLocality)"
          cols="auto"
        >
          <v-chip>
            {{ destinationDriver.addressLocality }}
          </v-chip>
        </v-col>
      </v-row>
      <v-divider />

      <!-- Monday -->
      <v-row
        v-if="monTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="3"
        >
          {{ $t('mon') }} {{ monTime }}
        </v-col>
        <!-- Second col :  switch -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="monCheck"
            color="success"
            inset
            hide-details
            class="mt-1 mb-1"
            @change="change"
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="7"
          :class="monCheck ? '' : 'font-italic'"
        >
          {{ monPeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="monTime" />

      <!-- Tuesday -->
      <v-row
        v-if="tueTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="3"
        >
          {{ $t('tue') }} {{ tueTime }}
        </v-col>
        <!-- Second col :  switch -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="tueCheck"
            color="success"
            inset
            hide-details
            class="mt-1 mb-1"
            @change="change"
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="7"
          :class="tueCheck ? '' : 'font-italic'"
        >
          {{ tuePeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="tueTime" />

      <!-- Wednesday -->
      <v-row
        v-if="wedTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="3"
        >
          {{ $t('wed') }} {{ wedTime }}
        </v-col>
        <!-- Second col :  switch -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="wedCheck"
            color="success"
            inset
            hide-details
            class="mt-1 mb-1"
            @change="change"
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="7"
          :class="wedCheck ? '' : 'font-italic'"
        >
          {{ wedPeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="wedTime" />

      <!-- Thursday -->
      <v-row
        v-if="thuTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="3"
        >
          {{ $t('thu') }} {{ thuTime }}
        </v-col>
        <!-- Second col :  switch -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="thuCheck"
            color="success"
            inset
            hide-details
            class="mt-1 mb-1"
            @change="change"
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="7"
          :class="thuCheck ? '' : 'font-italic'"
        >
          {{ thuPeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="thuTime" />

      <!-- Friday -->
      <v-row
        v-if="friTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="3"
        >
          {{ $t('fri') }} {{ friTime }}
        </v-col>
        <!-- Second col :  switch -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="friCheck"
            color="success"
            inset
            hide-details
            class="mt-1 mb-1"
            @change="change"
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="7"
          :class="friCheck ? '' : 'font-italic'"
        >
          {{ friPeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="friTime" />

      <!-- Saturday -->
      <v-row
        v-if="satTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="3"
        >
          {{ $t('sat') }} {{ satTime }}
        </v-col>
        <!-- Second col :  switch -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="satCheck"
            color="success"
            inset
            hide-details
            class="mt-1 mb-1"
            @change="change"
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="7"
          :class="satCheck ? '' : 'font-italic'"
        >
          {{ satPeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="satTime" />

      <!-- Sunday -->
      <v-row
        v-if="sunTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="3"
        >
          {{ $t('sun') }} {{ sunTime }}
        </v-col>
        <!-- Second col :  switch -->
        <v-col
          cols="2"
        >
          <v-switch
            v-model="sunCheck"
            color="success"
            inset
            hide-details
            class="mt-1 mb-1"
            @change="change"
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="7"
          :class="sunCheck ? '' : 'font-italic'"
        >
          {{ sunPeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="sunTime" />
    </v-container>
  </div>
</template>

<script>
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/utilities/RegularAsk/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    type: {
      type: Number,
      default: 1
    },
    originDriver: {
      type: Object,
      default: null
    },
    destinationDriver: {
      type: Object,
      default: null
    },
    originPassenger: {
      type: Object,
      default: null
    },
    destinationPassenger: {
      type: Object,
      default: null
    },
    fromDate: {
      type: String,
      default: null
    },
    maxDate: {
      type: String,
      default: null
    },
    monTime: {
      type: String,
      default: null
    },
    tueTime: {
      type: String,
      default: null
    },
    wedTime: {
      type: String,
      default: null
    },
    thuTime: {
      type: String,
      default: null
    },
    friTime: {
      type: String,
      default: null
    },
    satTime: {
      type: String,
      default: null
    },
    sunTime: {
      type: String,
      default: null
    },
    monCheckDefault: {
      type: Boolean,
      default:null
    },
    tueCheckDefault: {
      type: Boolean,
      default:null
    },
    wedCheckDefault: {
      type: Boolean,
      default:null
    },
    thuCheckDefault: {
      type: Boolean,
      default:null
    },
    friCheckDefault: {
      type: Boolean,
      default:null
    },
    satCheckDefault: {
      type: Boolean,
      default:null
    },
    sunCheckDefault: {
      type: Boolean,
      default:null
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
      monCheck: this.monCheckDefault,
      tueCheck: this.tueCheckDefault,
      wedCheck: this.wedCheckDefault,
      thuCheck: this.thuCheckDefault,
      friCheck: this.friCheckDefault,
      satCheck: this.satCheckDefault,
      sunCheck: this.sunCheckDefault
    };
  },
  computed: {
    computedStartDateFormat() {
      return this.startDate
        ? moment(this.startDate).format(this.$t("i18n.date.format.fullDate"))
        : "";
    },
    monPeriod() {
      if (!this.monCheck) return this.$t('notSelected');
      return this.monMin.format(this.$t("i18n.date.format.fullDate"))+(this.monMax && (moment.utc(this.monMax).isAfter(moment.utc(this.monMin))) ? " - " + this.monMax.format(this.$t("i18n.date.format.fullDate")) : '');
    },
    tuePeriod() {
      if (!this.tueCheck) return this.$t('notSelected');
      return this.tueMin.format(this.$t("i18n.date.format.fullDate"))+(this.tueMax && (moment.utc(this.tueMax).isAfter(moment.utc(this.tueMin))) ? " - " + this.tueMax.format(this.$t("i18n.date.format.fullDate")) : '');
    },
    wedPeriod() {
      if (!this.wedCheck) return this.$t('notSelected');
      return this.wedMin.format(this.$t("i18n.date.format.fullDate"))+(this.wedMax && (moment.utc(this.wedMax).isAfter(moment.utc(this.wedMin))) ? " - " + this.wedMax.format(this.$t("i18n.date.format.fullDate")) : '');
    },
    thuPeriod() {
      if (!this.thuCheck) return this.$t('notSelected');
      return this.thuMin.format(this.$t("i18n.date.format.fullDate"))+(this.thuMax && (moment.utc(this.thuMax).isAfter(moment.utc(this.thuMin))) ? " - " + this.thuMax.format(this.$t("i18n.date.format.fullDate")) : '');
    },
    friPeriod() {
      if (!this.friCheck) return this.$t('notSelected');
      return this.friMin.format(this.$t("i18n.date.format.fullDate"))+(this.friMax && (moment.utc(this.friMax).isAfter(moment.utc(this.friMin))) ? " - " + this.friMax.format(this.$t("i18n.date.format.fullDate")) : '');
    },
    satPeriod() {
      if (!this.satCheck) return this.$t('notSelected');
      return this.satMin.format(this.$t("i18n.date.format.fullDate"))+(this.satMax && (moment.utc(this.satMax).isAfter(moment.utc(this.satMin))) ? " - " + this.satMax.format(this.$t("i18n.date.format.fullDate")) : '');
    },
    sunPeriod() {
      if (!this.sunCheck) return this.$t('notSelected');
      return this.sunMin.format(this.$t("i18n.date.format.fullDate"))+(this.sunMax && (moment.utc(this.sunMax).isAfter(moment.utc(this.sunMin))) ? " - " + this.sunMax.format(this.$t("i18n.date.format.fullDate")) : '');
    },
    monMin() {
      if (this.monTime && this.monCheck) {
        return this.nextDay(1);
      } 
      return null;
    },
    tueMin() {
      if (this.tueTime && this.tueCheck) {
        return this.nextDay(2);
      } 
      return null;
    },
    wedMin() {
      if (this.wedTime && this.wedCheck) {
        return this.nextDay(3);
      } 
      return null;
    },
    thuMin() {
      if (this.thuTime && this.thuCheck) {
        return this.nextDay(4);
      } 
      return null;
    },
    friMin() {
      if (this.friTime && this.friCheck) {
        return this.nextDay(5);
      } 
      return null;
    },
    satMin() {
      if (this.satTime && this.satCheck) {
        return this.nextDay(6);
      } 
      return null;
    },
    sunMin() {
      if (this.sunTime && this.sunCheck) {
        return this.nextDay(7);
      } 
      return null;
    },
    monMax() {
      if (this.monTime && this.monCheck) {
        return this.lastDay(1);
      } 
      return null;
    },
    tueMax() {
      if (this.tueTime && this.tueCheck) {
        return this.lastDay(2);
      } 
      return null;
    },
    wedMax() {
      if (this.wedTime && this.wedCheck) {
        return this.lastDay(3);
      } 
      return null;
    },
    thuMax() {
      if (this.thuTime && this.thuCheck) {
        return this.lastDay(4);
      } 
      return null;
    },
    friMax() {
      if (this.friTime && this.friCheck) {
        return this.lastDay(5);
      } 
      return null;
    },
    satMax() {
      if (this.satTime && this.satCheck) {
        return this.lastDay(6);
      } 
      return null;
    },
    sunMax() {
      if (this.sunTime && this.sunCheck) {
        return this.lastDay(7);
      } 
      return null;
    }
  },
  watch: {
    fromDate() {
      this.change();
    },
    maxDate() {
      this.change();
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    nextDay(weekday) {
      const dayToCheck = moment(this.fromDate).isoWeekday();
      if (dayToCheck <= weekday) {
        return moment(this.fromDate).isoWeekday(weekday);
      } else {
        return moment(this.fromDate).add(1, 'weeks').isoWeekday(weekday);
      }
    },
    lastDay(weekday) {
      const dayToCheck = moment(this.maxDate).isoWeekday(weekday);
      if (dayToCheck.isSameOrBefore(moment(this.maxDate))) { 
        return dayToCheck;
      } else {
        return dayToCheck.subtract(1, 'weeks').isoWeekday(weekday);
      }
    },
    change() {
      let params = [];
      if (this.monCheck && this.monTime !== null) {
        params.push({
          "day": "mon",
          "time": this.monTime,
          "min": this.monMin.toISOString(),
          "max": this.monMax.isAfter(this.monMin) ? this.monMax.toISOString() : null
        });
      }
      if (this.tueCheck && this.tueTime !== null) {
        params.push({
          "day": "tue",
          "time": this.tueTime,
          "min": this.tueMin.toISOString(),
          "max": this.tueMax.isAfter(this.tueMin) ? this.tueMax.toISOString() : null
        });
      }
      if (this.wedCheck && this.wedTime !== null) {
        params.push({
          "day": "wed",
          "time": this.wedTime,
          "min": this.wedMin.toISOString(),
          "max": this.wedMax.isAfter(this.wedMin) ? this.wedMax.toISOString() : null
        });
      }
      if (this.thuCheck && this.thuTime !== null) {
        params.push({
          "day": "thu",
          "time": this.thuTime,
          "min": this.thuMin.toISOString(),
          "max": this.thuMax.isAfter(this.thuMin) ? this.thuMax.toISOString() : null
        });
      }
      if (this.friCheck && this.friTime !== null) {
        params.push({
          "day": "fri",
          "time": this.friTime,
          "min": this.friMin.toISOString(),
          "max": this.friMax.isAfter(this.friMin) ? this.friMax.toISOString() : null
        });
      }
      if (this.satCheck && this.satTime !== null) {
        params.push({
          "day": "sat",
          "time": this.satTime,
          "min": this.satMin.toISOString(),
          "max": this.satMax.isAfter(this.satMin) ? this.satMax.toISOString() : null
        });
      }
      if (this.sunCheck && this.sunTime !== null) {
        params.push({
          "day": "sun",
          "time": this.sunTime,
          "min": this.sunMin.toISOString(),
          "max": this.sunMax.isAfter(this.sunMin) ? this.sunMax.toISOString() : null
        });
      }
      this.$emit('change', params);
    }
  }
};
</script>
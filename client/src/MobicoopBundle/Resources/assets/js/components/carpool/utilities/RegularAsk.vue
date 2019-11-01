<template>
  <div>
    <v-container>
      <!-- Monday -->
      <v-row
        v-if="monTime"
        dense
        align="center"
      >
        <!-- First col : day and time -->
        <v-col
          cols="4"
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
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="6"
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
          cols="4"
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
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="6"
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
          cols="4"
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
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="6"
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
          cols="4"
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
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="6"
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
          cols="4"
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
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="6"
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
          cols="4"
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
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="6"
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
          cols="4"
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
          />
        </v-col>
        <!-- Third col : date ranges -->
        <v-col
          cols="6"
        >
          {{ sunPeriod }}
        </v-col>
      </v-row>
      <v-divider v-if="sunTime" />
    </v-container>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/carpool/utilities/RegularAsk.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/RegularAsk.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    type: {
      type: Number,
      default: 1
    },
    origin: {
      type: Object,
      default: null
    },
    destination: {
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
    period: {
      type: Number,
      default: 0
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
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
      monCheck: null,
      tueCheck: null,
      wedCheck: null,
      thuCheck: null,
      friCheck: null,
      satCheck: null,
      sunCheck: null
    };
  },
  computed: {
    computedStartDateFormat() {
      moment.locale(this.locale);
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
        return this.nextDay(0);
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
        return this.lastDay(0);
      } 
      return null;
    }
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
      const dayToCheck = moment(this.maxDate).isoWeekday();
      if (dayToCheck <= weekday) { 
        return moment(this.maxDate).isoWeekday(weekday);
      } else {
        return moment(this.maxDate).subtract(1, 'weeks').isoWeekday(weekday);
      }
    }
  }
};
</script>
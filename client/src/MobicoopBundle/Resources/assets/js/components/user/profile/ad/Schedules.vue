<template>
  <v-container
    fluid
    class="pa-0"
  >
    <v-row :no-gutters="noGutters">
      <v-col
        v-if="isRegular && !hasSameReturnTimes && !hasSameOutwardTimes"
        align="right"
      >
        <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('outward') }}</span>

        <v-icon class="accent--text text--darken-2 font-weight-bold">
          mdi-arrow-left-right
        </v-icon>

        <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('return') }}</span>
        <!--multiple times slot for outward and return-->
        <p
          class="font-italic mt-1 primary--text text--darken-3"
        >
          {{ $t('multipleTimesSlots') }}
        </p>
      </v-col>
      
      <v-col v-else>
        <v-row>
          <!--Outward-->
          <v-col
            v-if="isOutward"
            cols="6"
            class="py-0"
            :class="isRefined ? 'text-left' : 'text-right'"
          >
            <span
              v-if="!isRefined"
              class="accent--text text--accent font-weight-bold body-1"
            >{{ $t('outward') }}</span>

            <v-icon
              v-if="!isRefined"
              class="accent--text text--accent font-weight-bold"
            >
              mdi-arrow-right
            </v-icon>

            <span
              v-if="hasSameOutwardTimes"
              class="primary--text text--darken-2 body-1 text-capitalize"
            >
              {{ formatTime(outwardTimes ? outwardTimes[0] : outwardTime ? outwardTime : null) }}
            </span>
            <span
              v-else
              class="primary--text text--darken-2 body-1"
            >
              {{ $t('multipleTimesSlots') }}
            </span>
          </v-col>

          <!--Return-->
          <v-col
            v-if="isReturn"
            class="py-0"
            :align="isRegular ? 'right' : 'left'"
          >
            <span class="accent--text  font-weight-bold body-1">{{ $t('return') }}</span>

            <v-icon class="accent--text font-weight-bold">
              mdi-arrow-left
            </v-icon>

            <span
              v-if="hasSameReturnTimes"
              class="primary--text text--darken-2 body-1 text-capitalize"
            >
              {{ formatTime(returnTimes ? returnTimes[0] : returnTime ? returnTime : null) }}
            </span>
            <span
              v-else
              class="primary--text text--darken-2 body-1"
            >
              {{ $t('multipleTimesSlots') }}
            </span>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import moment from 'moment';
import Translations from "@translations/components/user/profile/ad/Schedules.js";

export default {
  i18n: {
    messages: Translations
  },
  props: {
    // for multiple computed times eg my ads
    outwardTimes: {
      type: Array,
      default: null
    },
    // for multiple computed times
    returnTimes: {
      type: Array,
      default: null
    },
    // for inline unique time by schedule eg my accepted carpools
    outwardTime: {
      type: String,
      default: null
    },
    // for inline unique time by schedule
    returnTime: {
      type: String,
      default: null
    },
    isRegular: {
      type: Boolean,
      default: false
    },
    isOutward: {
      type: Boolean,
      default: true
    },
    isReturn: {
      type: Boolean,
      default: false
    },
    dateTimeFormat: {
      type: String,
      default: "ui.i18n.time.format.hourMinute"
    },
    // if we want refined display of data for punctual carpools
    isRefined: {
      type: Boolean,
      default: false
    },
    noGutters: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      locale: this.$i18n.locale,
    };
  },
  computed: {
    hasSameOutwardTimes () {
      let isSame = true;
      if (this.outwardTimes) {
        const times = this.outwardTimes;
        const days = times.length;
        if (days < 2) {
          return isSame;
        }
        // start to 1 because we don't compare index 0 with index 0
        for (let i = 1; i < days; i++) {
          isSame = moment.utc(times[0]).isSame(times[i]);
          if (!isSame) {
            break;
          }
        }
      }
      return isSame;
    },
    hasSameReturnTimes () {
      let isSame = true;
      if (this.returnTimes) {
        const times = this.returnTimes;
        const days = times.length;
        if (days < 2) {
          return isSame;
        }
        // start to 1 because we don't compare index 0 with index 0
        for (let i = 1; i < days; i++) {
          isSame = moment.utc(times[0]).isSame(times[i]);
          if (!isSame) {
            break;
          }
        }
      }
      return isSame;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    formatTime(time) {
      return moment.utc(time).format(this.$t(this.dateTimeFormat));
    }
  }
}
</script>

<style scoped>

</style>
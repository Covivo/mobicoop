<template>
  <v-container
    fluid
    class="pa-0"
  >
    <v-row>
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
        <v-container
          fluid
          class="pa-0"
        >
          <v-row>
            <!--Outward-->
            <v-col
              v-if="isOutward"
              class="py-0"
              :align="isRegular ? 'right' : 'left'"
            >
              <span class="accent--text text--accent font-weight-bold body-1">{{ $t('outward') }}</span>

              <v-icon class="accent--text text--accent font-weight-bold">
                mdi-arrow-right
              </v-icon>

              <span
                v-if="hasSameOutwardTimes"
                class="primary--text text--darken-2 body-1 text-capitalize"
              >
                {{ formatTime(outwardTimes[0]) }}
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
                {{ formatTime(returnTimes[0]) }}
              </span>
              <span
                v-else
                class="primary--text text--darken-2 body-1"
              >
                {{ $t('multipleTimesSlots') }}
              </span>
            </v-col>
          </v-row>
        </v-container>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import moment from 'moment';
import {merge} from 'lodash';
import Translations from "@translations/components/user/profile/ad/Schedules.js";
import TranslationsClient from "@clientTranslations/components/user/profile/ad/Schedules.js";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    outwardTimes: {
      type: Array,
      default: () => []
    },
    returnTimes: {
      type: Array,
      default: () => []
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
      return isSame;
    },
    hasSameReturnTimes () {
      let isSame = true;
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
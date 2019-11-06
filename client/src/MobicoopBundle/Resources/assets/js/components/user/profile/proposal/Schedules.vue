<template>
  <v-container>
    <!--multiple times slot for outward and return-->
    <v-col
      v-if="!hasSameReturnTimes && !hasSameOutwardTimes"
      align="right"
    >
      <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('outward') }}</span>

      <v-icon class="accent--text text--darken-2 font-weight-bold">
        mdi-arrow-left-right
      </v-icon>

      <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('return') }}</span>

      <p
        class="font-italic mt-1 primary--text text--darken-3"
      >
        {{ $t('multipleTimesSlots') }}
      </p>
    </v-col>
    <v-col v-else>
      <v-row>
        <!--Outward-->
        <v-col>
          <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('outward') }}</span>

          <v-icon class="accent--text text--darken-2 font-weight-bold">
            mdi-arrow-right
          </v-icon>

          <span
            v-if="hasSameOutwardTimes"
            class="primary--text text--darken-3 body-1"
          >
            {{ formatTime(outwardTimes[0]) }}
          </span>
          <span
            v-else
            class="primary--text text--darken-3 body-1"
          >
            {{ $t('multipleTimesSlots') }}
          </span>
        </v-col>

        <!-- Return -->
        <v-col
          v-if="showReturn"
        >
          <span class="accent--text text--darken-2 font-weight-bold body-1">{{ $t('return') }}</span>

          <v-icon class="accent--text text--darken-2 font-weight-bold">
            mdi-arrow-left
          </v-icon>

          <span
            v-if="hasSameReturnTimes"
            class="primary--text text--darken-3 body-1"
          >
            {{ formatTime(returnTimes[0]) }}
          </span>
          <span
            v-else
            class="primary--text text--darken-3 body-1"
          >
            {{ $t('multipleTimesSlots') }}
          </span>
        </v-col>
      </v-row>
    </v-col>
  </v-container>
</template>

<script>
import moment from 'moment';
import {merge} from 'lodash';
import Translations from "@translations/components/user/profile/proposal/Schedules.js";
import TranslationsClient from "@clientTranslations/components/user/profile/proposal/Schedules.js";

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
    showReturn: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    hasSameOutwardTimes () {
      // moment.locale(this.locale);
      let isSame = true;
      // start to 1 because we don't compare index 0 with index 0
      for (let i = 1; i < this.outwardTimes.length; i++) {
        if (!this.outwardTimes[i]) {
          continue;
        }
        isSame = moment(this.outwardTimes[0]).isSame(this.outwardTimes[i]);
        if (!isSame) {
          break;
        }
      }
      return isSame;
    },
    hasSameReturnTimes () {
      // moment.locale(this.locale);
      let isSame = true;
      // start to 1 because we don't compare index 0 with index 0
      for (let i = 1; i < this.returnTimes.length; i++) {
        if (!this.returnTimes[i]) {
          continue;
        }
        isSame = moment(this.returnTimes[0]).isSame(this.returnTimes[i]);
        if (!isSame) {
          break;
        }
      }
      return isSame;
    }
  },
  methods: {
    formatTime(time) {
      return moment(time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
}
</script>

<style scoped>

</style>
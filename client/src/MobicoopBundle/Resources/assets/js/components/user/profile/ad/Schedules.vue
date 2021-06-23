<template>
  <v-container
    fluid
    class="pa-0"
  >
    <v-row :no-gutters="noGutters">
      <v-col
        v-if="isRegular && multipleOutward && multipleReturn"
        align="right"
      >
        <span class="accent--text text--darken-2 font-weight-bold text-body-1">{{ $t('outward') }}</span>

        <v-icon class="accent--text text--darken-2 font-weight-bold">
          mdi-arrow-left-right
        </v-icon>

        <span class="accent--text text--darken-2 font-weight-bold text-body-1">{{ $t('return') }}</span>
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
            v-if="outwardTime"
            cols="6"
            class="py-0"
            :class="isRefined || !hasDays ? 'text-left' : 'text-right'"
          >
            <span
              v-if="!isRefined"
              class="accent--text text--accent font-weight-bold text-body-1"
            >{{ $t('outward') }}</span>

            <v-icon
              v-if="!isRefined"
              class="accent--text text--accent font-weight-bold"
            >
              mdi-arrow-right
            </v-icon>

            <span
              v-if="!multipleOutward"
              class="primary--text text--darken-2 text-body-1 text-capitalize"
            >
              {{ dateTimeFormat === null ? outwardTime : formatTime(outwardTime) }}
            </span>
            <span
              v-else
              class="primary--text text--darken-2 text-body-1"
            >
              {{ $t('multipleTimesSlots') }}
            </span>
          </v-col>

          <!--Return-->
          <v-col
            v-if="returnTime"
            class="py-0"
            :align="isRegular ? 'right' : 'left'"
          >
            <span class="accent--text  font-weight-bold text-body-1">{{ $t('return') }}</span>

            <v-icon class="accent--text font-weight-bold">
              mdi-arrow-left
            </v-icon>

            <span
              v-if="!multipleReturn"
              class="primary--text text--darken-2 text-body-1 text-capitalize"
            >
              {{ dateTimeFormat === null ? returnTime : formatTime(returnTime) }}
            </span>
            <span
              v-else
              class="primary--text text--darken-2 text-body-1"
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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ad/Schedules/";

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
    outwardTime: {
      type: String,
      default: null
    },
    returnTime: {
      type: String,
      default: null
    },
    dateTimeFormat: {
      type: String,
      default: null
    },
    isRegular: {
      type: Boolean,
      default: false
    },
    multipleOutward: {
      type: Boolean,
      default: false
    },
    multipleReturn: {
      type: Boolean,
      default: false
    },
    // if we want refined display of data for punctual carpools
    isRefined: {
      type: Boolean,
      default: false
    },
    noGutters: {
      type: Boolean,
      default: false
    },
    // when schedule is placed near from RegularDaysSummary component
    hasDays: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
    };
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    formatTime(time) {
      return moment.utc(time) ? moment.utc(time).format(this.$t(this.dateTimeFormat)) : time;
    }
  }
}
</script>

<style scoped>

</style>
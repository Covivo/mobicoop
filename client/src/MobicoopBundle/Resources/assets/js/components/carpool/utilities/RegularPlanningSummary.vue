<template>
  <div>
    <v-row
      align="center"
    >
      <!-- Times -->

      <!-- Outward -->
      <v-col
        v-if="outwardTime"
        :cols="returnTrip ? '3' : '7'"
      >
        <v-row
          dense
        >
          <v-col
            v-if="returnTrip"
            cols="auto"
          >
            {{ $t('outward') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <v-icon
              slot="prepend"
            >
              mdi-arrow-right-circle
            </v-icon>
          </v-col>
          <v-col
            v-if="outwardTime"
            cols="auto"
          >
            {{ formatTime(outwardTime) }}
          </v-col>
          <v-col
            v-else
            cols="auto"
          >
            <span class="font-italic">{{ $t('multiple') }}</span>
          </v-col>
        </v-row>
      </v-col>

      <!-- Return -->
      <v-col
        v-if="returnTrip && (outwardTime || returnTime)"
        cols="3"
        offset="1"
      >
        <v-row
          dense
        >
          <v-col
            cols="auto"
          >
            {{ $t('return') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <v-icon
              slot="prepend"
            >
              mdi-arrow-left-circle
            </v-icon>
          </v-col>
          <v-col
            v-if="returnTime"
            cols="auto"
          >
            {{ formatTime(returnTime) }}
          </v-col>
          <v-col
            v-else
            cols="auto"
          >
            <span class="font-italic">{{ $t('multiple') }}</span>
          </v-col>
        </v-row>
      </v-col>

      <!-- Multi Outward & Return -->
      <v-col
        v-if="returnTrip && !outwardTime && !returnTime"
        cols="7"
      >
        <v-row
          dense
        >
          <v-col
            cols="auto"
          >
            {{ $t('outward') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <v-icon
              slot="prepend"
            >
              mdi-arrow-left-right
            </v-icon>
          </v-col>
          <v-col
            cols="auto"
          >
            {{ $t('return') }}
          </v-col>
          <v-col
            cols="auto"
          >
            <span class="font-italic">{{ $t('fullMultiple') }}</span>
          </v-col>
        </v-row>
      </v-col>

      <!-- Days -->
      <v-col
        cols="5"
        class="text-right"
      >
        <regular-days-summary 
          :mon-active="monActive"
          :tue-active="tueActive"
          :wed-active="wedActive"
          :thu-active="thuActive"
          :fri-active="friActive"
          :sat-active="satActive"
          :sun-active="sunActive"
        />
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/carpool/utilities/RegularPlanningSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/RegularPlanningSummary.json";
import RegularDaysSummary from "@components/carpool/utilities/RegularDaysSummary";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    RegularDaysSummary
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    outwardTime: {
      type: String,
      default: null
    },
    returnTrip: {
      type: Boolean,
      default: false
    },
    returnTime: {
      type: String,
      default: null
    },
    monActive: {
      type: Boolean,
      default: false
    },
    tueActive: {
      type: Boolean,
      default: false
    },
    wedActive: {
      type: Boolean,
      default: false
    },
    thuActive: {
      type: Boolean,
      default: false
    },
    friActive: {
      type: Boolean,
      default: false
    },
    satActive: {
      type: Boolean,
      default: false
    },
    sunActive: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      locale: this.$i18n.locale,
    };
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    formatTime(time) {
      return moment.utc(time).format(this.$t("ui.i18n.time.format.hourMinute"));
    }
  }
};
</script>
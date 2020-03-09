<template>
  <v-container
    fluid
    class="pa-0"
  >
    <v-row
      class="px-2"
    >
      <v-col
        cols="3"
        class="primary--text"
      >
        <span v-if="isDriver && seats && seats > 0">{{ bookedSeats }}/{{ seats }}&nbsp;{{ $tc('seat.booked', bookedSeats) }}</span>
        <span v-else-if="!isDriver && seats && seats > 0">{{ bookedSeats }}&nbsp;{{ $tc('seat.booked', bookedSeats) }}</span>
      </v-col>
    </v-row>
    <v-expansion-panels
      v-model="panelActive"
      :accordion="true"
      :tile="true"
      :flat="true"
    >
      <v-expansion-panel>
        <v-expansion-panel-header class="text-uppercase text-right">
          <v-row no-gutters>
            <v-col class="text-right font-weight-bold secondary--text">
              {{ panelActive === 0 ? $t('passengers.hide') :$t('passengers.show') }}
            </v-col>
          </v-row>
          <template v-slot:actions>
            <v-icon
              color="secondary"
              large
            >
              $expand
            </v-icon>
          </template>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <v-divider
            class="primary extra-divider"
          />
          <v-row
            v-for="(result, index) in ad.results"
            :key="index"
            no-gutters
          >
            <carpooler
              :result="result"
              :ad="ad"
              :user="user"
            />
            <v-divider
              v-if="index < ad.results.length - 1"
              class="primary lighten-5 ma-1"
            />
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/carpool/CarpoolFooter.js";
import TranslationsClient from "@clientTranslations/components/user/profile/carpool/CarpoolFooter.js";

import Carpooler from '@components/user/profile/carpool/Carpooler.vue';

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    Carpooler
  },
  props: {
    ad: {
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
      panelActive: false
    }
  },
  computed: {
    bookedSeats() {
      return this.ad.results.length
    },
    seats() {
      return this.isDriver ? this.ad.seatsDriver : this.ad.seatsPassenger
    },
    isDriver() {
      return this.ad.role === 1 || this.ad.role === 3
    }
  }
}
</script>

<style lang="scss" scoped>

.extra-divider {
  width: calc(100% + 64px) !important;
  max-width: unset !important;
  margin-left: -32px !important;
}

</style>
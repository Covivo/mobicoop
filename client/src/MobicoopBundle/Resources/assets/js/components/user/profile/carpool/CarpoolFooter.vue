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
      <v-col
        v-if="!isDriver && ad.asks[0].results[0].roundedPrice"
        cols="2"
        class="font-weight-bold primary--text headline text-right"
      >
        {{ ad.asks[0].results[0].roundedPrice }}€
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
              {{ panelActive === 0 ? hideMessage : showMessage }}
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
            v-for="(ask, index) in ad.asks"
            :key="index"
            no-gutters
          >
            <carpooler
              :result="ask.results[0]"
              :ask="ask"
              :user="user"
            />
            <v-divider
              v-if="index < ad.asks.length - 1"
              class="primary lighten-5 ma-1"
            />
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </v-container>
</template>

<script>
import Translations from "@translations/components/user/profile/carpool/CarpoolFooter.js";
import Carpooler from '@components/user/profile/carpool/Carpooler.vue';

export default {
  i18n: {
    messages: Translations
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
      return this.ad.asks.length
    },
    seats() {
      return this.isDriver ? this.ad.seatsDriver : this.ad.seatsPassenger
    },
    isDriver() {
      return this.ad.role === 1 || this.ad.role === 3
    },
    hideMessage() {
      return this.isDriver ? this.$t('passengers.hide') : this.$t('driver.hide');
    },
    showMessage() {
      return this.isDriver ? this.$t('passengers.show') : this.$t('driver.show');
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
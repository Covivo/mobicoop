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
        <!-- ad.driver is an empty Array if the carpooler is passenger -->
        <!-- ad.driver is an object if the carpooler is driver -->
        <!-- ad.passengers is always an array -->
        <span v-if="isDriver && ad.seats > 0">{{ $tc('seat.booked', seats, { seats: seats, bookedSeats: bookedSeats}) }}</span>
        <span v-else-if="!isDriver && seats > 0">{{ $tc('seat.booked', seats, { seats: seats, bookedSeats: bookedSeats}) }}</span>
      </v-col>
      <v-col
        v-if="!isDriver"
        cols="2"
        class="font-weight-bold primary--text text-h5 text-right"
      >
        {{ ad.driver.price }}€
      </v-col>
    </v-row>
    <v-expansion-panels
      v-model="panelActive"
      :accordion="true"
      :tile="true"
      :flat="true"
    >
      <v-expansion-panel>
        <v-expansion-panel-header
          class="text-uppercase text-right"
        >
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
            v-for="(carpooler, index) in carpoolers"
            :key="index"
            no-gutters
          >
            <!-- ad.driver is an empty Array if the carpooler is passenger -->
            <!-- ad.driver is an object if the carpooler is driver -->
            <!-- ad.passengers is always an array -->
            <carpooler
              :carpooler="carpooler"
              :passenger="ad.passengers.length>0"
              :driver="!Array.isArray(ad.driver)" 
              :frequency="carpooler.askFrequency"
              :user="user"
              :payment-electronic-active="paymentElectronicActive"
            />
            <v-divider
              v-if="index < carpoolers.length - 1"
              class="primary lighten-5 ma-1"
            />
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </v-container>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/carpool/CarpoolFooter/";
import Carpooler from '@components/user/profile/carpool/Carpooler.vue';

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
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
    },
    showCarpooler: {
      type: Boolean,
      default: false
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
  },
  data () {
    return {
      panelActive: false
    }
  },
  computed: {
    isDriver() {
      return this.ad.passengers.length>0;
    },
    bookedSeats() {
      return this.isDriver ? this.ad.passengers.length : 1;
    },
    seats() {
      return this.ad.seats;
    },
    hideMessage() {
      return this.isDriver ? this.$tc('passengers.hide',this.ad.passengers.length) : this.$t('driver.hide');
    },
    showMessage() {
      return this.isDriver ? this.$tc('passengers.show',this.ad.passengers.length) : this.$t('driver.show');
    },
    carpoolers() {
      return this.isDriver ? this.ad.passengers : [this.ad.driver]
    },
  },
  watch: {
    showCarpooler () {
      this.panelActive = this.showCarpooler == true ? 0 : false;
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
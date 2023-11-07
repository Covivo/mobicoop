<template>
  <div>
    <v-container v-if="punctualCarpools && punctualCarpools.length">
      <v-row
        v-for="ad in punctualCarpools"
        :key="ad.id"
      >
        <v-col cols="12">
          <Carpool
            :ad="ad"
            :is-archived="true"
            :user="user"
            :payment-electronic-active="paymentElectronicActive"
          />
        </v-col>
      </v-row>
    </v-container>
    <v-container v-if="regularCarpools && regularCarpools.length">
      <v-row>
        <v-col cols="12">
          <h2 class="h4 secondary--text">
            {{ $t("regular.title") }}
          </h2>
        </v-col>
      </v-row>
      <v-row
        v-for="ad in regularCarpools"
        :key="ad.id"
      >
        <v-col cols="12">
          <Carpool
            :ad="ad"
            :is-archived="true"
            :user="user"
            :payment-electronic-active="paymentElectronicActive"
          />
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/carpool/AcceptedCarpools/";
import Carpool from "@components/user/profile/carpool/Carpool.vue";

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
    Carpool
  },
  props: {
    carpools: {
      type: Array,
      default: () => []
    },
    chronologicalSorted: {
      type: Boolean,
      default: true
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    },
  },
  data() {
    return {
      punctualCarpools: [],
      regularCarpools: []
    }
  },
  watch: {
    carpools() {
      this.buildCarpools();
    }
  },
  mounted() {
    this.buildCarpools();
  },
  methods: {
    buildCarpools() {
      if (this.carpools && this.carpools.length) {
        this.setPunctualCarpools();
        this.setRegularCarpools();
      }
    },
    setPunctualCarpools() {
      this.punctualCarpools = [...this.carpools]
        .filter(carpool => 1 === carpool.frequency)
        .sort((a, b) => this.sortPunctualCarpools(a, b))

      if (this.chronologicalSorted) {
        this.punctualCarpools.reverse()
      }
    },
    setRegularCarpools() {
      this.regularCarpools = [...this.carpools]
        .filter(carpool => 2 === carpool.frequency);
    },
    getStartAdDate(ad) {
      return new Date(`${ad.driver.fromDate ? ad.driver.fromDate : ad.outwardDate} ${ad.driver.pickUpTime ? ad.driver.pickUpTime : ad.outwardTime}`)
    },
    sortPunctualCarpools(a, b) {
      const date_A = this.getStartAdDate(a)
      const date_B = this.getStartAdDate(b)

      return date_A - date_B
    }
  }
}
</script>

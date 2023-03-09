<template>
  <v-card>
    <!-- ad.driver is an empty Array if the carpooler is passenger -->
    <!-- ad.driver is an object if the carpooler is driver -->
    <!-- ad.passengers is always an array -->
    <ad-header
      :is-driver="ad.passengers.length>0"
      :is-passenger="!Array.isArray(ad.driver)"
      :is-pausable="ad.frequency === 2"
      :is-archived="isArchived"
      :has-accepted-ask="!Array.isArray(ad.driver) || ad.passengers.length>0"
      :has-ask="ad.asks"
      :ad-id="ad.id"
      :payment-item-id="ad.paymentItemId"
      :ad-frequency="frequency"
      :is-carpool="true"
      :payment-status="ad.paymentStatus"
      :payment-week="ad.paymentItemWeek"
      :unpaid-date="ad.unpaidDate"
      :payment-electronic-active="paymentElectronicActive"
      @activePanel="activePanel()"
    />

    <v-card-text v-if="frequency === 2">
      <ad-content-regular
        :ad="ad"
        :is-carpool="true"
      />
    </v-card-text>

    <v-card-text v-else>
      <ad-content-punctual
        :ad="ad"
        :is-refined="true"
        :is-carpool="true"
      />
    </v-card-text>

    <v-divider class="primary lighten-5 my-2 divider95" />

    <v-card-actions class="py-0">
      <carpool-footer
        :id="ad.id"
        :seats="ad.seats"
        :price="ad.price"
        :id-message="lastMessageId"
        :ad="ad"
        :user="user"
        :show-carpooler="showCarpooler"
        :payment-electronic-active="paymentElectronicActive"
        :communities="ad.communities ? ad.communities : []"
      />
    </v-card-actions>
  </v-card>
</template>

<script>
import AdHeader from '@components/user/profile/ad/AdHeader.vue';
import CarpoolFooter from '@components/user/profile/carpool/CarpoolFooter.vue';
import AdContentRegular from '@components/user/profile/ad/AdContentRegular.vue';
import AdContentPunctual from '@components/user/profile/ad/AdContentPunctual.vue';

export default {
  components: {
    AdHeader,
    CarpoolFooter,
    AdContentRegular,
    AdContentPunctual
  },
  props: {
    ad: {
      type: Object,
      default: () => {
      }
    },
    isArchived: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      lastMessageId: null,
      showCarpooler: false
    }
  },
  computed:{
    frequency(){
      if(!Array.isArray(this.ad.driver) && this.ad.driver.askFrequency !== undefined){
        return this.ad.driver.askFrequency;
      }
      return this.ad.frequency;
    }
  },
  methods: {
    activePanel() {
      this.showCarpooler = true;
    }
  }
}
</script>

<style scoped lang="scss">
.divider95 {
  width: 95%;
  margin: auto;
}
</style>

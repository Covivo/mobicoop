<template>
  <v-card>
    <ad-header
      :is-driver="isDriver"
      :is-passenger="isPassenger"
      :is-pausable="isRegular"
      :is-paused="isPaused"
      :is-archived="isArchived"
      :has-accepted-ask="hasAtLeastOneAcceptedAsk"
      :has-ask="hasAtLeastOneAsk"
      :ad-id="ad.outward.id"
      @ad-deleted="adDeleted"
      @pause-ad="pauseAd"
    />
    
    <v-card-text v-if="isRegular">
      <ad-content-regular :ad="ad" />
    </v-card-text>
      
    <v-card-text v-else>
      <ad-content-punctual :ad="ad" />
    </v-card-text>

    <v-divider class="primary lighten-5" />
      
    <v-card-actions class="py-0">
      <ad-footer
        v-if="!isPaused"
        :id="ad.outward.id"
        :seats="(isDriver) ? ad.outward.seatsDriver : ad.outward.seatsPassenger"
        :price="(isDriver) ? ad.outward.outwardDriverPrice : ad.outward.outwardPassengerPrice"
        :id-message="lastMessageId"
        :nb-matchings="ad.outward.results.length"
      />
    </v-card-actions>
  </v-card>
</template>

<script>
import AdHeader from '@components/user/profile/ad/AdHeader.vue';
import AdFooter from '@components/user/profile/ad/AdFooter.vue';
import AdContentRegular from '@components/user/profile/ad/AdContentRegular.vue';
import AdContentPunctual from '@components/user/profile/ad/AdContentPunctual.vue';
  
export default {
  components: {
    AdHeader,
    AdFooter,
    AdContentRegular,
    AdContentPunctual
  },
  props: {
    ad: {
      type: Object,
      default: () => {}
    },
    isArchived: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      hasAtLeastOneAsk: false,
      hasAtLeastOneAcceptedAsk: false,
      lastMessageId: null,
      isPaused: this.ad.outward.paused
    }
  },
  computed: {
    isDriver () {
      return this.ad.outward.role === 1 || this.ad.outward.role === 3
    },
    isPassenger () {
      return (this.ad.outward.role === 2 || this.ad.outward.role === 3) && this.ad.outward.solidaryExclusive != 1
    },
    isRegular () {
      return this.ad.outward.frequency === 2;
    },
    hasReturn () {
      return !this.ad.outward.oneWay;
    }
  },
  mounted () {
    this.checkAsks();
  },
  methods: {
    checkAsks () {
      this.ad.outward.results.forEach(result => {
        if (result.pendingAsk) {
          this.hasAtLeastOneAsk = true;
        }
        if (result.acceptedAsk) {
          this.hasAtLeastOneAcceptedAsk = true;
        } 
      });
    },
    adDeleted(isArchived, id) {
      this.$emit('ad-deleted', isArchived, id)
    },
    pauseAd(pauseAd) {
      this.isPaused = pauseAd;
     
    }
  }
}
</script>

<style scoped lang="scss">
</style>
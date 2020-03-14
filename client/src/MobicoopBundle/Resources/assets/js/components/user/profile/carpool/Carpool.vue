<template>
  <v-card>
    <ad-header
      :is-driver="isDriver"
      :is-passenger="isPassenger"
      :is-pausable="isRegular"
      :is-archived="isArchived"
      :has-accepted-ask="hasAtLeastOneAcceptedAsk"
      :has-ask="hasAtLeastOneAsk"
      :ad-id="ad.id"
      :is-carpool="true"
    />

    <v-card-text v-if="isRegular">
      <ad-content-regular :ad="ad" />
    </v-card-text>

    <v-card-text v-else>
      <ad-content-punctual
        :ad="ad"
        :is-refined="true"
      />
    </v-card-text>

    <v-divider class="primary lighten-5 my-2 divider95" />

    <v-card-actions class="py-0">
      <carpool-footer
        :id="ad.id"
        :seats="(isDriver) ? ad.seatsDriver : ad.seatsPassenger"
        :price="(isDriver) ? ad.outwardDriverPrice : ad.outwardPassengerPrice"
        :id-message="lastMessageId"
        :ad="ad"
        :user="user"
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
    }
  },
  data() {
    return {
      hasAtLeastOneAsk: false,
      hasAtLeastOneAcceptedAsk: false,
      lastMessageId: null
    }
  },
  computed: {
    isDriver() {
      return this.ad.role === 1 || this.ad.role === 3
    },
    isPassenger() {
      return (this.ad.role === 2 || this.ad.role === 3) && this.ad.solidaryExclusive !== 1
    },
    isRegular() {
      return this.ad.frequency === 2;
    },
    hasReturn() {
      return !this.ad.oneWay;
    }
  },
  mounted() {
    this.checkAsks();
  },
  methods: {
    checkAsks() {
      this.ad.results.forEach(result => {
        if (result.pendingAsk) {
          this.hasAtLeastOneAsk = true;
        }
        if (result.acceptedAsk) {
          this.hasAtLeastOneAcceptedAsk = true;
        }
      });
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
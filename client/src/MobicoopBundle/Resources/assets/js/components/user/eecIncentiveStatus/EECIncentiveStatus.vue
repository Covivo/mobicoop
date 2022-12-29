<template>
  <div>
    <div v-if="!loading">
      <EECIncentiveInitiateSubscription
        v-if="!subscriptionInitiated"
        :confirmed-phone-number="confirmedPhoneNumber"
        :driving-licence-number-filled="drivingLicenceNumberFilled"
      />
      <EECIncentiveAdditionalInformations
        v-else
        :long-distance-subscriptions="subscriptions.longDistanceSubscriptions"
        :short-distance-subscriptions="subscriptions.shortDistanceSubscriptions"
        :pending-proofs="subscriptions.nbPendingProofs"
        :refused-proofs="subscriptions.nbRejectedProofs"
      />
    </div>
    <div v-else>
      <v-skeleton-loader
        class="mx-auto"
        max-width="100%"
        type="paragraph@2"
      />
    </div>
  </div>
</template>

<script>
import maxios from "@utils/maxios";
import EECIncentiveInitiateSubscription from '@components/user/eecIncentiveStatus/EECIncentiveInitiateSubscription';
import EECIncentiveAdditionalInformations from '@components/user/eecIncentiveStatus/EECIncentiveAdditionalInformations';
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components:{
    EECIncentiveInitiateSubscription,
    EECIncentiveAdditionalInformations
  },
  props: {
    confirmedPhoneNumber:{
      type: Boolean,
      default: false
    },
    drivingLicenceNumberFilled:{
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      subscriptions:null,
      loading: false
    }
  },
  computed:{
    subscriptionInitiated(){
      if(this.subscriptions && (this.subscriptions.longDistanceSubscriptions || this.subscriptions.shortDistanceSubscriptions)){
        return true;
      }
      return false;
    }
  },
  mounted(){
    this.getMyCeeSubscriptions();
  },
  methods:{
    getMyCeeSubscriptions(){
      this.loading = true;
      maxios.get(this.$t("routes.getMyCeeSubscriptions"))
        .then(res => {
          this.subscriptions = res.data;
          this.loading = false;
        })
        .catch(function (error) {

        });
    }
  },
};
</script>


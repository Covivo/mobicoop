<template>
  <div>
    <EECIncentiveInitiateSubscription
      v-if="!subscriptionInitiated"
      :confirmed-phone-number="confirmedPhoneNumber"
      :driving-licence-number-filled="drivingLicenceNumberFilled"
    />
    <EECIncentiveAdditionalInformations v-else />
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
      subscriptions:null
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
      maxios.get(this.$t("routes.getMyCeeSubscriptions"))
        .then(res => {
          this.subscriptions = res.data;
        })
        .catch(function (error) {

        });
    }
  },
};
</script>


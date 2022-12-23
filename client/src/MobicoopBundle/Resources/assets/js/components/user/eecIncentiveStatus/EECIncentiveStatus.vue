<template>
  <div>
    <EECIncentiveInitiateSubscription v-if="!subscriptionInitiated" />
  </div>
</template>

<script>
import maxios from "@utils/maxios";
import EECIncentiveInitiateSubscription from '@components/user/eecIncentiveStatus/EECIncentiveInitiateSubscription';
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
    EECIncentiveInitiateSubscription
  },
  props: {
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


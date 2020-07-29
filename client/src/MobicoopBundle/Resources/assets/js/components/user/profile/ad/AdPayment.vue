<template>
  <v-container>
    <div>
      <div v-if="showUnpaid && unpaid">
        Impayé
      </div>
      <v-btn
        color="primary"
        rounded
        :outlined="outlined"
        :disabled="disabled"
        :href="$t('link', {'id':this.itemId,'frequency':this.frequency,'type':this.type})"
      >
        {{ displayPaymentStatus }}
      </v-btn>          
    </div>
  </v-container>
</template>      
<script>

import Translations from "@translations/components/user/profile/ad/AdPayment.json";

export default {
  i18n: {
    messages: Translations
  },
  props: {
    isDriver: {
      type: Boolean,
      default: false
    },
    isPassenger: {
      type: Boolean,
      default: false
    },
    paymentStatus: {
      type: Number,
      default: null
    },
    outlined: {
      type: Boolean,
      default: false
    },
    showUnpaid: {
      type: Boolean,
      default: false
    },
    itemId: {
      type: Number,
      default: 1
    },
    frequency: {
      type: Number,
      default: 1
    },
  },
  data(){
    return {
      disabled:false,
      unpaid:false,
    }
  },
  computed: {
    displayPaymentStatus(){
      let status = this.getStatus(this.paymentStatus);
      return (this.isDriver) ? this.$t('driver.'+status) : this.$t('passenger.'+status);
    },
    type(){
      return (this.isDriver) ? 2 : 1;
    }
  },
  methods:{
    getStatus(paymentStatus){
      if(paymentStatus=="0" || paymentStatus=="3"){
        // Pending or Unpaid
        if(this.paymentStatus=="3"){this.unpaid = true}
        return "pending";
      }
      else if(paymentStatus=="1" || paymentStatus=="2" || paymentStatus=="4"){
        // Paid
        this.disabled = true;
        return "paid";
      }
    }
  }
}
</script>
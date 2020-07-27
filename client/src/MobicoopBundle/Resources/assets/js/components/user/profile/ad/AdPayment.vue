<template>
  <v-container>
    <div>
      <v-btn
        color="primary"
        rounded
        :outlined="outlined"
        :disabled="disabled"
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
    }
  },
  data(){
    return {
      disabled:false
    }
  },
  computed: {
    displayPaymentStatus(){
      let status = "";
      if(this.paymentStatus=="0" || this.paymentStatus=="3"){
        // Pending or Unpaid
        status = "pending";
      }
      else if(this.paymentStatus=="1" || this.paymentStatus=="2" || this.paymentStatus=="4"){
        // Paid
        status = "paid";
        this.disabled = true;
      }

      return (this.isDriver) ? this.$t('driver.'+status) : this.$t('passenger.'+status);
    }
  }
}
</script>
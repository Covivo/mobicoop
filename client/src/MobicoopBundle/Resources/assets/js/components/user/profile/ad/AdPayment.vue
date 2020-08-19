<template>
  <v-container>
    <v-row>
      <v-col
        v-if="showUnpaid && unpaid"
        class="warning--text"
        align-self="center"
      >
        <v-icon class="warning--text">
          mdi-alert-outline
        </v-icon> {{ $t('unpaid') }}
      </v-col>
      <v-col v-if="!hideButton">
        <v-btn
          color="primary"
          rounded
          :outlined="outlined"
          :disabled="disabled"
          :href="$t('link', {'id':this.paymentItemId,'frequency':this.frequency,'type':this.type,'week':this.week})"
        >
          {{ displayPaymentStatus }}
        </v-btn>
      </v-col>         
    </v-row>
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
    unpaidDate: {
      type: String,
      default: null
    },
    showUnpaid: {
      type: Boolean,
      default: false
    },
    hideButton: {
      type: Boolean,
      default: false
    },
    paymentItemId: {
      type: Number,
      default: 1
    },
    frequency: {
      type: Number,
      default: 1
    },
    week: {
      type: Number,
      default: null
    }    
  },
  data(){
    return {
      disabled:false,
      unpaid:(this.unpaidDate) ? true : false,
    }
  },
  computed: {
    displayPaymentStatus(){
      let status = this.getStatus(this.paymentStatus);
      return (this.isDriver) ? this.$t('driver.'+status) : this.$t('passenger.'+status);
    },
    type(){
      return (this.isDriver) ? 2 : 1;
    },
  },
  methods:{
    getStatus(paymentStatus){
      if(paymentStatus=="0" || paymentStatus=="3"){
        // Pending or Unpaid
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
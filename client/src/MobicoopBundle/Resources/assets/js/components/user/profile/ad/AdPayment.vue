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
        <v-tooltip bottom>
          <template v-slot:activator="{ on }">
            <v-btn
              color="primary"
              rounded
              :outlined="outlined"
              :disabled="disabled"
              @click="action()"
              v-on="(isDriver === false) ? on : {}"
            >
              {{ displayPaymentStatus }}
            </v-btn>
          </template>
          <span>{{ displayTooltips }}</span>
        </v-tooltip>
      </v-col>         
    </v-row>
  </v-container>
</template>      
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ad/AdPayment/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    isDriver: {
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
    paymentItemId: {
      type: Number,
      default: null
    },
    frequency: {
      type: Number,
      default: null
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
  },
  data(){
    return {
      disabled:false,
      unpaid:(this.unpaidDate) ? true : false,
    }
  },
  computed: {
    hideButton() {
      return this.paymentStatus == -1;
    },
    status() {
      return this.getStatus(this.paymentStatus);
    },  
    displayPaymentStatus(){
      return (this.isDriver) ? this.$t('driver.'+this.status) : (this.paymentElectronicActive) ? this.$t('passenger.'+this.status) : this.$t('passenger.pendingElectronicNotActive');
    },
    displayTooltips(){
      return (this.paymentElectronicActive) ? this.$t('tooltip.paymentElectronicActive') : this.$t('tooltip.paymentElectronicNotActive')
    },
    type(){
      return (this.isDriver) ? 2 : 1;
    },
  },
  mounted(){
    this.getStatus(this.paymentStatus);
  },
  methods:{
    getStatus(paymentStatus){
      switch (this.paymentStatus) {
      case 1: 
        return "pending";
      default: 
        this.disabled = true;
        return "paid";
      }
    },
    action() {
      if (this.paymentItemId && this.frequency) {
        window.location.href = this.$t('route', {'id':this.paymentItemId,'frequency':this.frequency,'type':this.type});
      } else {
        this.$emit('activePanel');
      }
    }
  }
}
</script>
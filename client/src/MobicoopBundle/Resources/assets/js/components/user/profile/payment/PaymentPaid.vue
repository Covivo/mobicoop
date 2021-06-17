<template>
  <div>
    <v-row justify="center">
      <v-col
        cols="4"
        class="text-left"
      >
        <v-card :loading="loading">
          <v-card-title>{{ $t('title') }}</v-card-title>
          <v-card-text v-if="error">
            {{ $t('noPaymentPaymentId') }}
          </v-card-text>
          <v-card-text v-else-if="paymentStatus==2 || paymentFailed">
            <v-icon color="error">
              mdi-alert-outline
            </v-icon>
            {{ $t('paymentFailed') }}
          </v-card-text>
          <v-card-text v-else-if="paymentStatus==1">
            <v-icon color="success">
              mdi-check-circle-outline
            </v-icon>
            {{ $t('paymentSucceeded') }}
          </v-card-text>
          <v-card-text v-else-if="paymentStatus==0">
            {{ $t('wait.line1') }}<br>{{ $t('wait.line2') }}
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col
        v-if="tipsEncouragement"
        cols="5"
        class="text-left"
      >
        {{ $t('tipsEncouragement.text', {'platformName':platformName}) }}
        <a
          :href="tipsEncouragementLink"
          target="_blank"
          title="a"
        >{{ $t('tipsEncouragement.textLink') }}</a>.
      </v-col>
    </v-row>
  </div>
</template>
<script>
import axios from "axios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/payment/PaymentPaid/";

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
    paymentPaymentId: {
      type: Number,
      default: null
    },
    colorLoading: {
      type: String,
      default: "secondary"
    },
    platformName: {
      type: String,
      default: ""
    },
    tipsEncouragement: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      loading:this.colorLoading,
      error:false,
      paymentStatus:0,
      paymentFailed:false
    }
  },
  mounted(){
    if(!this.paymentPaymentId || this.paymentPaymentId==-1){
      this.error = true;
    }
    else{    
      this.checkPaymentStatus();
    }
  },
  methods:{
    checkPaymentStatus(){
      var loop = 0;
      var maxloop = 4;
      const self = this;
      function check(){
        if(self.paymentStatus>0 || loop>=maxloop){
          self.loading = false;
          clearInterval(loopCheck);
          if(self.paymentStatus==0) self.paymentFailed = true; // Payment still not validated. For display, we fail it
          return;
        }

        let params = {
          "paymentPaymentId":self.paymentPaymentId
        }
        axios.post(self.$t("checkUrl"),params)
          .then(response => {
            //console.log(response.data);
            self.paymentStatus = response.data.status;
          })
          .catch(function (error) {
            console.error(error);
          });

        loop++;
      }      
      var loopCheck = setInterval(check, 3000);
    }
    
  }
}
</script>
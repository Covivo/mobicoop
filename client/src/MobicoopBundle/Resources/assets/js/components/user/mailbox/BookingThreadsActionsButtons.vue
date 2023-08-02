<template>
  <v-main>
    <div>
      <v-card
        class="primary mb-8"
        flat
      >
        <v-row
          align="center"
        >
          <v-col
            cols="2"
            class="text-center"
            justify="center"
          >
            <v-img
              class="ml-2"
              src="/images/pages/mailBox/interopWhite.png"
              contain
              height="23"
            />
          </v-col>
          <v-col
            class="white--text justify-center"
            cols="10"
          >
            <p
              v-html="$t('bookingAlert', {'platform': carpoolerOperator})"
            />
          </v-col>
        </v-row>
      </v-card>
    </div>
    <div v-if="checkBookingStatus==1">
      <v-btn
        color="primary"
        small
        dark
        rounded
        depressed
        :loading="loading"
        dense
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(waitingPassengerConfirmation)"
      >
        {{ $t('button.bookingCarpoolAsDriver') }}
      </v-btn> 
    </div>
    <div v-if="checkBookingStatus==2">
      <v-btn
        color="primary"
        small
        dark
        rounded
        depressed
        :loading="loading"
        dense
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(waitingDriverConfirmation)"
      >
        {{ $t('button.bookingCarpoolAsPassenger') }}
      </v-btn> 
    </div>
    <div v-if="checkBookingStatus==3">
      <v-btn
        class="mr-12"
        color="primary"
        width="30%"
        small
        dark
        rounded
        depressed
        :loading="loading"
        dense
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(confirmed)"
      >
        {{ $t('button.accept') }}
      </v-btn> 
      <v-btn
        color="error"
        width="30%"
        small
        dark
        rounded
        depressed
        :loading="loading"
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(cancelled)"
      >
        {{ $t('button.refuse') }}
      </v-btn>
    </div>
    <div
      v-if="checkBookingStatus==4"
    >
      <v-card
        color="warning"
        class="white--text mb-8"
        flat
      >
        {{ $t('bookingPending') }}
      </v-card>
      <v-btn
        color="error"
        width="30%"
        small
        dark
        rounded
        depressed
        :loading="loading"
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(cancelled)"
      >
        {{ $t('button.cancel') }}
      </v-btn>
    </div>
    <div
      v-if="checkBookingStatus==5"
    >
      <v-card
        color="primary"
        class="white--text mb-8"
        flat
      >
        {{ $t('bookingAccepted') }}
      </v-card>
    </div>
    <div
      v-if="checkBookingStatus==6"
    >
      <v-card
        color="error"
        class="white--text mb-8"
        flat
      >
        {{ $t('bookingRefused') }}
      </v-card>
    </div>
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/BookingThreadsActionsButtons/";

const INITIATED = 'INITIATED';
const WAITING_PASSENGER_CONFIRMATION = 'WAITING_PASSENGER_CONFIRMATION';
const WAITING_DRIVER_CONFIRMATION = 'WAITING_DRIVER_CONFIRMATION';
const CONFIRMED = 'CONFIRMED';
const CANCELLED = 'CANCELLED';
const COMPLETED_PENDING_VALIDATION = 'COMPLETED_PENDING_VALIDATION';
const VALIDATED = 'VALIDATED';

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    status:{
      type:String,
      default:null
    },
    loadingBtn:{
      type:Boolean,
      default:false
    },
    isRoleDriver:{
      type:Boolean,
      default:false
    },
    operator:{
      type:String,
      default:null
    }
   
  },
  data(){
    return {
      loading:this.loadingBtn,
      initiated: INITIATED,
      waitingDriverConfirmation: WAITING_DRIVER_CONFIRMATION,
      waitingPassengerConfirmation: WAITING_PASSENGER_CONFIRMATION,
      confirmed: CONFIRMED,
      cancelled: CANCELLED,
      completedPendingValidation:COMPLETED_PENDING_VALIDATION,
      validated: VALIDATED,
      carpoolerOperator: this.operator,
    }
  },
  computed:{
    checkBookingStatus(){
      switch (this.status) {
      case this.initiated:
        if (this.isRoleDriver){
          return 1;
        } 
        return 2;
      case this.waitingDriverConfirmation:
        if (this.isRoleDriver){
          return 3;
        } 
        return 4;
      case this.waitingPassengerConfirmation:
        if (this.isRoleDriver){
          return 4;
        } 
        return 3; 
      case this.confirmed:
        return 5;
      case this.completedPendingValidation:
        return 5;
      case this.validated:
        return 5;     
      case this.cancelled:
        return 6;  
      default:
        return null;
      }
    },
  },
  watch:{
    loadingBtn(){
      this.loading = this.loadingBtn
    }
  },
  methods:{
    updateBookingStatus(status){
      this.$emit("updateBookingStatus",{status:status});
    },
    
  }
}
</script>
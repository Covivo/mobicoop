<template>
  <v-main>
    <div v-if="checkBookingStatus==1">
      <v-btn
        class="mr-12"
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
        Demander un covoiturage
      </v-btn> 
    </div>
    <div v-if="checkBookingStatus==2">
      <v-btn
        class="mr-12"
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
        Demander un covoiturage
      </v-btn> 
    </div>
    <div v-if="checkBookingStatus==3">
      <v-btn
        class="mr-12"
        color="primary"
        small
        dark
        rounded
        depressed
        :loading="loading"
        dense
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(confirmed)"
      >
        Accepter un covoiturage
      </v-btn> 
      <v-btn
        color="error"
        small
        dark
        rounded
        depressed
        :loading="loading"
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(cancelled)"
      >
        Refuser
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
        {{ $t('askPending') }}
      </v-card>
      <v-btn
        color="error"
        small
        dark
        rounded
        depressed
        :loading="loading"
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateBookingStatus(cancelled)"
      >
        Annuler
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
        Covoiturage accépté
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
        Covoiturage refusé
      </v-card>
    </div>
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadsActionsButtons/";

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
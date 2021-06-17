<template>
  <v-main>
    <!-- The Ask is just Initiated -->
    <!-- Only the Ask User can make a formal request of carpool -->

    <div
      v-if="status==1 && canUpdateAsk"
    >
      <v-btn
        v-if="driver"
        class="mb-2"
        color="primary"
        large
        dark
        rounded
        depressed
        :loading="loading"
        dense
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateStatus(2,'driver')"
      >
        {{ $t('button.askCarpoolAsDriver') }}
      </v-btn> 

      <v-btn
        v-if="passenger"
        class="myButton"
        color="primary"
        large
        dark
        rounded
        depressed
        :loading="loading"
        style="letter-spacing: -0.15px;white-space: normal;"
        @click="updateStatus(3,'passenger')"
      >
        {{ $t('button.askCarpoolAsPassenger') }}
      </v-btn>
    </div>
    <div v-if="status==1 && !canUpdateAsk && carpoolContext">
      <v-card-text>{{ $t('onlyAskUser') }}</v-card-text>
    </div>
    <!-- end ask just Initiated -->

    <!-- The Ask is pending -->
    <!-- If you are the ask user you cannot accept or delined -->
    <div v-if="(status==2 || status==3) && canUpdateAsk">
      <v-btn
        class="mr-12"
        width="30%"
        color="success"
        rounded                 
        small
        dark
        depressed
        :loading="loading"
        @click="updateStatus((status==2) ? 5 : 4)"
      >
        {{ $t('button.accept') }}
      </v-btn> 
      <v-btn
        class="ml-12"
        width="30%"
        color="error"
        rounded
        small
        dark
        depressed
        :loading="loading"
        @click="updateStatus((status==2) ? 7 : 6)"
      >
        {{ $t('button.refuse') }}
      </v-btn>       
    </div>
    <div v-else-if="(status==2 || status==3)">
      <v-card
        color="warning"
        class="white--text"
        flat
      >
        {{ $t('askPending') }}
      </v-card>
    </div>
    <!-- End the Ask is pending -->


    <!-- The Ask is accepted -->
    <div v-if="status==4 || status==5">
      <v-card
        color="success"
        class="white--text"
        flat
      >
        {{ $t('askAccepted') }}
      </v-card>
    </div>
    <!-- The Ask is refused -->
    <div v-if="status==6 || status==7">
      <v-card
        color="error"
        class="white--text"
        flat
      >
        {{ $t('askRefused') }}
      </v-card>
    </div>
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadsActionsButtons/";

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
      type:Number,
      default:1
    },
    canUpdateAsk:{
      type:Boolean,
      default:false
    },
    regular:{
      type:Boolean,
      default:false
    },
    loadingBtn:{
      type:Boolean,
      default:false
    },
    driver:{
      type:Boolean,
      default:false
    },
    passenger:{
      type:Boolean,
      default:false
    },
    carpoolContext:{
      type:Boolean,
      default:false
    },
  },
  data(){
    return {
      loading:this.loadingBtn
    }
  },
  watch:{
    loadingBtn(){
      this.loading = this.loadingBtn
    }
  },
  methods:{
    updateStatus(status,role=null){
      this.$emit("updateStatus",{status:status,role:role});
    }
  }
}
</script>

<style lang="scss">
div > button > span
  {
    flex: 1 1 auto !important;
  }
</style>
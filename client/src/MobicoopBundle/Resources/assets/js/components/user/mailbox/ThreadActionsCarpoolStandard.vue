<template>
  <v-main>
    <v-card
      class="pa-2 text-center"
      :hidden="hideClickIcon"
    >
      <v-card
        v-if="test"
        class="mb-3"
        flat
      >
        <div>
          <v-btn
            class="mb-2"
            color="primary"
            large
            dark
            rounded
            depressed
            :loading="loading"
            dense
            style="letter-spacing: -0.15px;white-space: normal;"
          >
            Test
          </v-btn> 

          <v-btn
            class="myButton"
            color="primary"
            large
            dark
            rounded
            depressed
            :loading="loading"
            style="letter-spacing: -0.15px;white-space: normal;"
          >
            Test
          </v-btn>
        </div>
      </v-card>

     
      <!-- Only visible for carpool -->
      <v-card
        v-if="test && !loading"
        class="mb-3"
        flat
      >
        <v-chip
          class="secondary mb-4"
        >
          <v-icon
            left
            color="white"
          >
            mdi-swap-horizontal
          </v-icon>
          {{ $t('roundTrip') }}
        </v-chip>
      </v-card>
      <!-- <v-card v-else-if="!loading">
        <v-card-text>
          {{ $t("notLinkedToACarpool") }}
        </v-card-text>
      </v-card> -->
      <v-skeleton-loader
        v-if="loading"
        ref="skeleton"
        type="card"
        class="mx-auto"
      />
      <v-skeleton-loader
        v-if="loading"
        ref="skeleton"
        type="actions"
        class="mx-auto"
      />
    </v-card>
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadActions/";
import maxios from "@utils/maxios";
import moment from "moment";

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
    idBooking: {
      type: String,
      default: null
    },
    idUser: {
      type: Number,
      default: null
    },
    emailUser: {
      type: String,
      default: null
    },
    idRecipient: {
      type: Number,
      default: null
    },
    loadingInit: {
      type: Boolean,
      default: false
    },
    refresh: {
      type: Boolean,
      default: false
    },
    loadingBtn: {
      type: Boolean,
      default: false
    },
    recipientName: {
      type: String,
      default: null
    },
    recipientAvatar: {
      type: String,
      default: null
    }
  },
  data(){
    return{
      locale: localStorage.getItem("X-LOCALE"),
      loading:this.loadingInit,
      dataLoadingBtn:this.loadingBtn,
      infosComplete:[],
      chosenRole:null,
      hideClickIcon : false,
      loadingBlock: false,
      dataBlockerId: this.blockerId,
      showProfileDialog: false,
      test: this.idBooking,
    }
  },
  
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods:{
    refreshInfos() {
      this.hideClickIcon = false;
    }
    
  }
}
</script>

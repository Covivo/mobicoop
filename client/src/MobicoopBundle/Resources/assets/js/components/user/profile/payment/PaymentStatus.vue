<template>
  <v-row
    justify="center"
    class="ma-0 pa-0 mt-2"
  >
    <v-col
      cols="9"
      align="center"
    >
      <v-card
        flat
      >
        <v-card-text
          v-if="!canBePaid"
          class="pa-0 font-weight-bold"
        >
          <v-icon 
            color="warning"
            class="text-h3"
          >
            mdi-alert-circle
          </v-icon>
          {{ $t('cannotBePaid') }}
        </v-card-text>
        <v-card-text
          v-else
          class="pa-0 font-weight-bold"
        >
          <v-icon 
            color="success"
            class="text-h3"
          >
            mdi-check-circle
          </v-icon>
          {{ $t('canBePaid') }}
        </v-card-text>
      </v-card>      
    </v-col>
  </v-row>
</template>
<script>
import {merge} from "lodash";
import {messages_en, messages_fr} from "@translations/components/user/profile/payment/PaymentStatus/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/user/profile/payment/PaymentStatus/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props: {
    canBePaid:{
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
    }
  }    
}
</script>
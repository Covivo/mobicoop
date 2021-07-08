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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/payment/PaymentStatus/";

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
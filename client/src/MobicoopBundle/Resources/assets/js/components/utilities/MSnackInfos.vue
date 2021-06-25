<template>
  <v-snackbar
    v-model="snackbar"
    :top="top"
    timeout="-1"
  >
    <span v-html="text" />

    <template v-slot:action="{ attrs }">
      <v-btn
        :color="btnColor"
        text
        v-bind="attrs"
        @click="snackbar = false"
      >
        {{ $t('close') }}
      </v-btn>
    </template>
  </v-snackbar>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MSnackInfos/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props:{
    active: {
      type: Boolean,
      default: false
    },
    text:{
      type: String,
      default: ""
    },
    top:{
      type: Boolean,
      default: true
    },
    btnColor:{
      type: String,
      default: "primary"
    }
  },
  data() {
    return {
      snackbar:this.active
    }
  }    
}
</script>

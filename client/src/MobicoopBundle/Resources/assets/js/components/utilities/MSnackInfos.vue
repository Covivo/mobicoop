<template>
  <v-snackbar
    v-model="snackbar"
    :top="top"
    timeout="-1"
  >
    <span v-html="$t('text')" />

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

import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MSnackInfos/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/MSnackInfos/";


let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
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

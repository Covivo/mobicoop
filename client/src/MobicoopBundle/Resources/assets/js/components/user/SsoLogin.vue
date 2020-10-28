<template>
  <div
    v-if="buttonIcon"
    :style="'max-width:'+maxWidth+'px;'"
  >
    <v-tooltip bottom>
      <template v-slot:activator="{ on, attrs }">
        <v-img
          id="buttonWithImage"
          :src="buttonIcon"
          style="cursor:pointer"
          v-bind="attrs"
          v-on="on"
          @click="click"
        />
      </template>
      <span>{{ $t('useSsoService', {'service':service}) }}</span>
    </v-tooltip>
  </div>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/user/SsoLogin/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/user/SsoLogin/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props:{
    url:{
      type: String,
      default: null
    },
    buttonIcon:{
      type: String,
      default: null
    },
    service:{
      type: String,
      default: null
    },
    maxWidth:{
      type: Number,
      default:200
    }
  },
  methods:{
    click(){
      window.location.href = ((this.url) ? this.url : '/');
    }
  }
}
</script>
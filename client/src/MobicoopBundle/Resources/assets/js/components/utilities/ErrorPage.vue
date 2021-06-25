<template>
  <v-container>
    <v-row class="justify-center">
      <v-col cols="10">
        <v-row>
          <v-col cols="12">
            <h1>{{ errorTitle }}</h1>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            {{ errorDesc }}
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            {{ $t('returnHome.text') }} 
            <a
              href="/"
              :title="$t('returnHome.link')"
            >{{ $t('returnHome.link') }}</a>.
          </v-col>
        </v-row>        
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/ErrorPage/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/ErrorPage/";
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
    }
  },
  props:{
    code:{
      type: Number,
      default: null
    }
  },
  computed:{
    errorDesc(){
      if(this.$t(this.code+".desc") == this.code+".desc"){
        return this.$t("default.desc");
      }
      else{
        return this.$t(this.code+".desc");
      }
    },
    errorTitle(){
      if(this.$t(this.code+".title") == this.code+".title"){
        return this.$t("default.title");
      }
      else{
        return this.$t(this.code+".title");
      }
    }
  }
}
</script>
<template>
  <v-container>
    <v-row class="justify-center">
      <v-col cols="5">
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
import {messages_en, messages_fr} from "@translations/components/utilities/ErrorPage/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
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
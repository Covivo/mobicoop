<template>
  <v-menu
    offset-y
  >
    <template v-slot:activator="{ on }">
      <v-btn
        rounded
        text
        v-on="on"
      >
        <v-icon>
          mdi-translate
        </v-icon>
        <v-icon>mdi-chevron-down</v-icon>
      </v-btn>
    </template>
    <v-list>
      <v-list-item
        v-for="(item, index) in languagesList"
        :key="index"
      >
        <v-list-item-title @click="selectLanguage(item)">
          {{ item.name }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>
<script>
import axios from "axios";
import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/base/MHeaderProfile/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/base/MHeaderProfile/"

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
    userLanguage:{
      type: String,
      default: "fr_FR"
    },
    languages: {
      type: Array,
      default: null
    }
  },
  data(){
    return {
      languagesList: this.languages,
    }
  },
  methods:{
    selectLanguage(item) {
      this.$emit('languageSelected', item.locale);
      axios.post('/setLanguage', item);
    },
  }
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
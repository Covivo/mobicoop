<template>
  <v-menu
    offset-y
    open-on-hover
  >
    <template v-slot:activator="{ on }">
      <v-btn
        rounded
        text
        v-on="on"
      >
        <v-icon color="secondary">
          mdi-flag
        </v-icon>
        <v-icon>mdi-chevron-down</v-icon>
      </v-btn>
    </template>
    <v-list>
      <v-list-item
        v-for="(item, index) in items"
        :key="index"
      >
        <v-list-item-title @click="selectLanguage(item)">
          {{ item.lang }} {{ item.text }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>
<script>
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
  },
  data(){
    return {
      items: [
        { lang: "fr", text: "Français" },
        { lang: "de", text: "Deutch" },
        { lang: "ba", text: "Basque" },
        { lang: "en", text: "English" },
      ]
    }
  },
  methods:{
    selectLanguage(item) {
      this.$emit('languageSelected', item.lang)
    },
  }
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
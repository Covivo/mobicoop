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
        <v-icon>
          mdi-translate
        </v-icon>
        <v-icon>mdi-chevron-down</v-icon>
      </v-btn>
    </template>
    <v-list
      rounded
    >
      <v-list-item-group
        v-model="selectedLanguage"
        color="primary"
      >
        <v-list-item
          v-for="(item, key, i) in languagesList"
          :key="i"
        >
          <v-list-item-title
            @click="selectLanguage(item, key)"
          >
            {{ item }}
          </v-list-item-title>
        </v-list-item>
      </v-list-item-group>
    </v-list>
  </v-menu>
</template>
<script>
import axios from "axios";
import {messages_en, messages_fr} from "@translations/components/base/MHeaderLanguage/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  props:{
    language: {
      type: String,
      default: "fr"
    },
    languages: {
      type: Object,
      default: () => {}
    }
  },
  data(){
    return {
      selectedLanguage: null,
      languagesList: this.languages,
    }
  },
  methods:{
    selectLanguage(item, key) {
      this.selectedLanguage = item
      this.$emit('languageSelected', key);
      axios.post(this.$t('urlToSelectLanguage'), {locale:key});
    },
  }
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
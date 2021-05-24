<template>
  <v-menu
    v-if="enabled"
    offset-y
    open-on-hover
  >
    <template v-slot:activator="{ on }">
      <v-btn
        rounded
        text
        :class="textClass"
        v-on="on"
      >
        <p class="mt-4">
          {{ diplayedLanguage }}
        </p>
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
          v-for="(item, key) in languages"
          :key="key"
          :value="key"
          @click="selectLanguage(item, key)"
        >
          <v-list-item-title>
            {{ item }}
          </v-list-item-title>
        </v-list-item>
      </v-list-item-group>
    </v-list>
  </v-menu>
</template>
<script>
import axios from "axios";
import {messages_en, messages_fr, messages_eu} from "@translations/components/base/MHeaderLanguage/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    language: {
      type: String,
      default: ''
    },
    languages: {
      type: Object,
      default: () => {}
    },
    textClass: {
      type: String,
      default: null
    }
  },
  data(){
    return {
      selectedLanguage: this.language,
      diplayedLanguage: this.language,
      // check if we have more than 1 language
      enabled: Object.keys(this.languages).length > 1
    }
  },
  methods:{
    selectLanguage(item, key) {
      this.selectedLanguage = item;
      this.diplayedLanguage = key;
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
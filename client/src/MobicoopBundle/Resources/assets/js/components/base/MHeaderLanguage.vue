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
          v-for="(item, i) in langaugesList"
          :key="i"
        >
          <v-list-item-title
            @click="selectLanguage(item)"
          >
            {{ item.name }}
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
      type: Array,
      default: null
    }
  },
  data(){
    return {
      selectedLanguage: null,
      langaugesList: this.languages,
    
    }
  },
  created() {
    this.langaugesList.forEach((language, index) => {
      if (this.language == language.locale) {
        this.selectedLanguage = index
      }
    });
  },
  methods:{
    selectLanguage(item) {
      this.selectedLanguage = item
      this.$emit('languageSelected', item.locale);
      axios.post(this.$t('urlToSelectLanguage'), item);
    },
  }
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
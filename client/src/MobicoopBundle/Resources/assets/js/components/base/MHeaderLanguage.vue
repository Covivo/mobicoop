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
          {{ displayedLanguage }}
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
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/base/MHeaderLanguage/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
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
      displayedLanguage: this.language,
      // check if we have more than 1 language
      enabled: Object.keys(this.languages).length > 1
    }
  },
  mounted(){
    if(localStorage.getItem('X-LOCALE') && localStorage.getItem('X-LOCALE') !== ''){
      this.selectedLanguage = localStorage.getItem('X-LOCALE');
      this.displayedLanguage = localStorage.getItem('X-LOCALE');
      if(this.language !== this.selectedLanguage){
        // If not the default language of the platform, we change it
        this.$emit('languageSelected', this.selectedLanguage);
      }
    }
    else{
      // Init local storage if there is no previous setup
      // To do : Make it customable by instance
      localStorage.setItem('X-LOCALE','fr');
      this.selectedLanguage = localStorage.getItem('X-LOCALE');
      this.displayedLanguage = localStorage.getItem('X-LOCALE');
      this.$emit('languageSelected', localStorage.getItem('X-LOCALE'));
    }
  },
  methods:{
    selectLanguage(item, key) {
      this.selectedLanguage = item;
      this.displayedLanguage = key;
      localStorage.setItem('X-LOCALE',key);

      this.$emit('languageSelected', key);
      maxios.post(this.$t('urlToUpdateLanguage'), {language:key});
    },
  }
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
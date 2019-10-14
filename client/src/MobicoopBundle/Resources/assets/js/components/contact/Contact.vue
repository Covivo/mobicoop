<template>
  <v-content>
    <p>salut</p>
  </v-content>
</template>

<script>
import {merge} from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/home/HomeSearch.json";
import TranslationsClient from "@clientTranslations/components/home/HomeSearch.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  data () {
    return {

    }
  }
}
</script>

<style scoped>

</style>

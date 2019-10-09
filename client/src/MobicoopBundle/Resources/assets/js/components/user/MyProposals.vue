<template>
  <v-content>
    <v-container
      fluid     
    >
      <v-layout
        justify-center
        text-center
      >
        <v-flex xs10>
          <v-alert type="info">
            {{ $t("ui.infos.notAvailableYet") }}
          </v-alert>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>
<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/Profile.json";
import TranslationsClient from "@clientTranslations/components/user/Profile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
  },
  data(){
    return {
      
    }
  }
}
</script>
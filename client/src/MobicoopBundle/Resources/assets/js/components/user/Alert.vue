<template>
  <div>zig</div>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/Alert.json";
import TranslationsClient from "@clientTranslations/components/user/Alert.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  }
    
}
</script>
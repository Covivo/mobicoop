<template>
  <cookie-law
    :button-text="$t('iUnderstand')"
  >
    <div slot="message">
      {{ $t('messageInfo') }}.
      <a :href="$t('link')">
        {{ $t('privacyPolicy') }}
      </a>
    </div>
  </cookie-law>
</template>
 
<script>
import {merge} from "lodash";
import CookieLaw from 'vue-cookie-law';
import Translations from "@translations/components/utilities/Cookies.json";

let TranslationsMerged = merge(Translations);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: { 
    CookieLaw
  }
}
</script>
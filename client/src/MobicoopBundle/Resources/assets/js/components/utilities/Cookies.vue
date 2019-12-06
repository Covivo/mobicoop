<template>
  <cookie-law
    theme="custom-theme"
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
 <style lang="scss" >
   .Cookie--custom-theme {
     .Cookie__button {
     background: #666;
     padding: 8px;
     color: #fff;
     border-width: 2px 4px;
     border-radius: 40px;
     font-size: 1em;
     margin: 16px;
     }
     .Cookie__buttons {
       align-self: center;
       white-space: nowrap;
     }
     padding:20px;
     background: white;
     }
 </style>
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
import {messages_fr, messages_en} from "@translations/components/utilities/Cookies/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/utilities/Cookies/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
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
<template>
  <!-- <cookie-law
    theme="custom-theme"
    :button-text="$t('iUnderstand')"
  >
    <div slot="message">
      {{ $t('messageInfo') }}.
      <a :href="$t('link')">
        {{ $t('privacyPolicy') }}
      </a>
    </div>
  </cookie-law> -->
  <div />
</template>
 
<script>
//import CookieLaw from 'vue-cookie-law';
import {messages_en, messages_fr} from "@translations/components/utilities/Cookies/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  components: { 
    
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
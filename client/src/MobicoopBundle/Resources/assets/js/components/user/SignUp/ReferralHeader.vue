<template>
  <div>{{ referral }}</div>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/ReferralHeader/";
export default {
  props: {
    referral: {
      type: String,
      default: null,
    }
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_fr,
      'fr': messages_eu,
      'eu': messages_nl
    },
  },
}
</script>

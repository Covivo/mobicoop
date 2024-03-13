<template>
  <v-alert
    type="info"
    color="primary"
  >
    {{ $t(referral) }}
  </v-alert>
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
      'nl': messages_nl,
      'fr': messages_fr,
      'eu': messages_eu
    },
  },
}
</script>

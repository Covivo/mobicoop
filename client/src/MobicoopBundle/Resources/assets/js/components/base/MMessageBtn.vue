<template>
  <v-btn
    text
    rounded
    depressed
    :href="$t('route')"
    :class="textColorClass"
  >
    <v-badge
      :value="unreadMessageNumber>0 ? true : false"
      color="secondary"
      :content="unreadMessageNumber"
    >
      {{ $t('label') }}
    </v-badge>
  </v-btn>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/base/MMessageBtn/";
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
    unreadMessageNumber:{
      type: Number,
      default: 0
    },
    textColorClass: {
      type: String,
      default: "white--text title text-none"
    },
  }
}
</script>
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
import {messages_en, messages_fr} from "@translations/components/base/MMessageBtn/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
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
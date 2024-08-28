<template>
  <div>
    <v-row>
      <v-col cols="6">
        <v-img
          :src="`/${$t(`platform.bannerUrl`)}`"
          width="auto"
          height="150px"
          contain
          class="mb-5"
        />
      </v-col>
      <v-col cols="6">
        <v-img
          :src="`/${$t(`${referral}.bannerUrl`)}`"
          width="auto"
          height="150px"
          contain
          class="mb-5"
        />
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-alert
          type="info"
          color="primary"
          v-html="$t(`${referral}.text`)"
        />
      </v-col>
    </v-row>
  </div>
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
  }
}
</script>

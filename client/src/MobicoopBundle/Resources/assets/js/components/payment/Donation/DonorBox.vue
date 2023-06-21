<template>
  <v-container>
    <v-row>
      <v-col class="text-center">
        <h1>{{ $t('title') }}</h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <p>{{ $t('text') }}</p>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <h3>{{ $t('subtitle') }}</h3>
      </v-col>
    </v-row>
    <v-row>
      <v-col class="text-center">
        <iframe
          :src="$t('src')"
          name="donorbox"
          allowpaymentrequest="allowpaymentrequest"
          seamless="seamless"
          frameborder="0"
          scrolling="no"
          height="900px"
          width="100%"
          style="max-width: 420px; min-width: 250px; max-height:none!important"
        />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/payment/Donation/DonorBox/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
  },
  props: {
  },
  data() {
    return {
    };
  },
};
</script>

<template>
  <v-main>
    <v-container>
      <v-row
        justify="center"
      >
        <v-col
          cols="4"
          md="5"
          align="center"
          class="justify-center"
        >
          <iframe
            :src="$t('externalRoute')"
            width="100%"
            height="540px"
            frameborder="0"
            scrolling="no"
          />
        </v-col>
        <v-col
          cols="8"
          md="7"
          class="mt-12"
        >
          <v-row class="mt-12">
            <h4>{{ $t('textDetails.title') }}</h4>
            <p
              class="mt-8"
              v-html="$t('textDetails.p1')"
            />
            <p v-html="$t('textDetails.p2', {'url':getUrl()})" />
            <p v-html="$t('textDetails.p3')" />
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/utilities/PlatformWidget/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/utilities/PlatformWidget/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  components: {
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props:{
    community:{
      type: Object,
      default: null
    },
  },
  methods:{
    getUrl() {
      return window.location.protocol +"//"+ window.location.host + this.$t('externalRoute');
    }
  }
}
</script>

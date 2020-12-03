<template>
  <v-main>
    <v-container>
      <v-row
        justify="center"
      >
        <v-col
          align="center"
          class="justify-center"
        >
          <iframe
            :src="$t('externalRoute')"
            frameborder="0"
            width="100%"
            scrolling="yes"
            :height="height"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
<script>
import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/utilities/MBlogPost/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/utilities/MBlogPost/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  name:"MGetBlogPost",
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props:{
    height: {
      type: String,
      default: ""
    }
  }
}
</script>

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
          <p class="font-weight-bold black--text text-left mt-1 mb-n1">
            {{ $t("title") }}
          </p> 
          <v-divider />
          <p class="font-weight-bold black--text text-left text-h5 mt-4">
            {{ title }}
          </p>

          <v-img
            contain
            min-width="250"
            max-width="600"
            min-height="150"
            max-height="150"
            :src="image"
          />
          <p class="mt-4 text-left">
            {{ description }}
          </p>

          <a
            :href="$t('feedUrl')"
            target="_blank"
          >            
            <p class="text-left">{{ $t("readMore") }}</p>
          </a>
          <v-divider />
          <p class="font-weight-thin black--text text-left mt-3 text-body-2">
            {{ pubDate }}
          </p>
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
  name: "MBlogpost",
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props: {
    title:{
      type: String,
      default: null
    },
    image: {
      type: String,
      default: null
    },
    description: {
      type: String,
      default: null
    },
    pubDate: {
      type: String,
      default: null
    }
  }
}
</script>

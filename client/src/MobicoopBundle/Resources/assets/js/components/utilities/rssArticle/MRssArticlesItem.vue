<template>
  <v-col
    cols="4"
  >
    <v-card
      elevation="2"
      class="mx-auto my-3 text-left"
      min-height="628px"
    >
      <v-card-title>
        <p class="font-weight-bold black--text text-left text-h5 mt-4">
          {{ article.title }}
        </p>
      </v-card-title>
      <v-img
        contain
        min-width="250"
        max-width="600"
        min-height="150"
        max-height="150"
        :src="article.image"
      />
      <v-card-text>
        <p class="mt-4 text-left">
          {{ article.description }}
        </p>
        <v-btn
          :href="$t('feedUrl')"
          target="_blank"
          rounded
          color="secondary"
          class="hidden-md-and-down white--text"
        >
          {{ $t("readMore") }}
        </v-btn>
      </v-card-text>
    </v-card>
  </v-col>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/utilities/rssArticle/RssArticlesItem/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/utilities/rssArticle/RssArticlesItem/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
export default {
  name: "MRssArticlesItem",
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props:{
    article: {
      type: Array,
      default: null
    }
  }
}
</script>

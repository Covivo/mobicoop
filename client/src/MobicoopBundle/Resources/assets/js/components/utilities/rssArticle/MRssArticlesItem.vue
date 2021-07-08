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
        <h3 class="font-weight-bold black--text text-left mt-4">
          {{ article.title }}
        </h3>
      </v-card-title>
      <v-img
        v-if="article.image"
        contain
        min-width="250"
        max-width="600"
        min-height="150"
        max-height="150"
        :src="article.image"
      />
      <div
        v-else-if="article.iframe"
        class="ma-0 pa-0"
        v-html="article.iframe"
      />
      <v-img
        v-else
        src="/images/avatarsDefault/avatar.svg"
        class="grey lighten-2"
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
          class="white--text"
        >
          {{ $t("readMore") }}
        </v-btn>
      </v-card-text>
    </v-card>
  </v-col>
</template>
<script>


import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/rssArticle/RssArticlesItem/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/rssArticle/RssArticlesItem/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);
export default {
  name: "MRssArticlesItem",
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  props:{
    article: {
      type: Object,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
  h3{
    font-size: 1.8rem;
    line-height: 1.2;
  }
</style>


<template>
  <v-container>
    <v-row
      v-for="article in articles"
      :key="article.id"
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
          {{ article.title }}
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
          {{ article.description }}
        </p>

        <a
          :href="$t('feedUrl')"
          target="_blank"
        >            
          <p class="text-left">{{ $t("readMore") }}</p>
        </a>
        <v-divider />
        <p class="font-weight-thin black--text text-left mt-3 text-body-2">
          {{ article.pubDate }}
        </p>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

import axios from "axios";
import {messages_en, messages_fr} from "@translations/components/article/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  props: {
    articles: {
      type: Object,
      default: null
    },
  },
  data () {
    return {
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
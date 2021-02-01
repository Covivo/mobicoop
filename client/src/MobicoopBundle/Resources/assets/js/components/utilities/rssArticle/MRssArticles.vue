<template>
  <div>
    <v-row>
      <v-col
        class="text-left"
        cols="6"
      />
      <v-spacer />
    </v-row>
    <v-row justify="center">
      <MRssArticlesItem
        v-for="(article, index) in articles"
        :key="index"
        :article="article"
      />
    </v-row>
  </div>
</template>


<script>
import axios from "axios";
import { merge } from "lodash";
import MRssArticlesItem from "@components/utilities/rssArticle/MRssArticlesItem";
import {messages_en, messages_fr} from "@translations/components/utilities/rssArticle/RssArticle/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/utilities/rssArticle/RssArticle/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  name: "MRssArticles",
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  components: {
    MRssArticlesItem
  },
  data() {
    return {
      articles: null,
    };
  },
  mounted() {
    this.getRssArticle();
  },
  methods: {
    getRssArticle(){
      axios.post(this.$t("externalRoute"))
        .then(response => {
          // console.error(response.data);
          this.articles = response.data;
        });
    }
  }
}
</script>

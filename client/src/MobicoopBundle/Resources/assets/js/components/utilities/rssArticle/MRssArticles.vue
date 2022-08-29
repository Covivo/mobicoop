<template>
  <div>
    <v-row>
      <v-col
        class="text-left"
        cols="6"
      />
      <v-spacer />
    </v-row>
    <v-row
      v-if="!loading"
      justify="center"
    >
      <MRssArticlesItem
        v-for="(article, index) in articles"
        :key="index"
        :article="article"
      />
    </v-row>
    <v-row v-else>
      <v-col
        v-for="n in 3"
        :key="n"
        cols="4"
      >
        <v-skeleton-loader
          class="mx-auto"
          max-width="100%"
          type="card"
        />
      </v-col>
    </v-row>
  </div>
</template>


<script>
import maxios from "@utils/maxios";
import { merge } from "lodash";
import MRssArticlesItem from "@components/utilities/rssArticle/MRssArticlesItem";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/rssArticle/RssArticle/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/rssArticle/RssArticle/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  name: "MRssArticles",
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  components: {
    MRssArticlesItem
  },
  data() {
    return {
      articles: null,
      loading: true
    };
  },
  mounted() {
    this.getRssArticle();
  },
  methods: {
    getRssArticle(){
      maxios.post(this.$t("externalRoute"))
        .then(response => {
          // console.error(response.data);
          this.articles = response.data;
          this.loading = false;
        });
    }
  }
}
</script>

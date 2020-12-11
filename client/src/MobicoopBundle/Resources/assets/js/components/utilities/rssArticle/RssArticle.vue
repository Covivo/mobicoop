<template>
  <v-main>
    <v-container>
      <v-row
        v-for="rssArticle in rssArticles"
        :key="rssArticle.id"
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
            {{ rssArticle.title }}
          </p>

          <v-img
            contain
            min-width="250"
            max-width="600"
            min-height="150"
            max-height="150"
            :src="rssArticle.image"
          />
          <p class="mt-4 text-left">
            {{ rssArticle.description }}
          </p>

          <a
            :href="$t('feedUrl')"
            target="_blank"
          >            
            <p class="text-left">{{ $t("readMore") }}</p>
          </a>
          <v-divider />
          <p class="font-weight-thin black--text text-left mt-3 text-body-2">
            {{ rssArticle.pubDate }}
          </p>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>

<script>
import { merge } from "lodash";
import axios from "axios";
import {messages_en, messages_fr} from "@translations/components/utilities/RssArticle/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/utilities/RssArticle/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);


export default {
  name: "RssArticle",
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  data() {
    return {
      rssArticles:[]
    };
  },
  mounted() {
    this.getRssArticle();
  },
  methods: {
    getRssArticle(){
      axios.post(this.$t("externalRoute"))
        .then(response => {
          this.rssArticles = response.data;
        });      
    }
  }
}
</script>

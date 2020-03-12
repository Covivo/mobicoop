<template>
  <v-container>
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        v-if="article"
        cols="10"
      >
        <h1 class="display-2 primary--text bold">
          {{ article.title }}
        </h1>
        <v-row
          v-for="section in article.sections"
          :key="section"
        >
          <v-col>
            <h2 class="display-1 secondary--text">
              {{ section.title }}
            </h2>
            <h3 class="headline">
              {{ section.subtitle }}
            </h3>
            <v-row
              v-for="paragraph in section.paragraphs"
              :key="paragraph"
            >
              <v-col>
                <div
                  v-html="paragraph.text"
                />
              </v-col>
            </v-row>       
          </v-col>
        </v-row>    
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

import axios from "axios";
import Translations from "@translations/components/article/MArticle.json";

export default {
  i18n: {
    messages: Translations,
  },
  props: {
    articleId: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      article: null,
    }
  },
  mounted(){
    let params = {
      'articleId':this.articleId
    }
    axios.post(this.$t("getArticle"), params)
      .then(res => {
        this.article = res.data;
      });
      
  },
}
</script>
<template>
  <v-container>
    <v-row
      align="center"
      justify="center"
      class="text-justify"
    >
      <v-col
        v-if="article"
        class="article"
        cols="10"
      >
        <h1 class="text-h4 primary--text text-center font-weight-bold">
          {{ article.title }}
        </h1>
        <v-row
          v-for="section in article.sections"
          :key="section.id"
        >
          <v-col>
            <h2
              class="text-h6 font-weight-bold"
            >
              {{ section.title }}
            </h2>
            <h3 class="text-h5">
              {{ section.subtitle }}
            </h3>
            <v-row
              v-for="paragraph in section.paragraphs"
              :key="paragraph.id"
            >
              <v-col>
                <div
                  class="ma-n3"
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

import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/article/MArticle/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    articleId: {
      type: Number,
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
    maxios.post(this.$t("getArticle"), params)
      .then(res => {
        this.article = res.data;
      });

  },
}
</script>

<style lang="scss" scoped>

.article {
  // background-color: red;
  .ma-n3{
    // background-color: yellow;
  }
}

.article .ma-n3 ::v-deep table {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  display: table;
  // background-color: chartreuse;
}

.article .ma-n3 {
  overflow-x: auto;
}
.article .ma-n3 ::v-deep table {
  min-width: 600px;
  width: 100% !important;
  max-width: 100%;
  // background-color: chartreuse;
}


</style>

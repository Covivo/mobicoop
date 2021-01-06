<template>
  <v-container fluid>
    <v-row
      dense
      justify="center"
      class="mt-10"
    >
      <v-col
        cols="8"
        class="text-right"
      >
        <h2 class="success--text font-italic">
          {{ $t('news.title') }}
        </h2>
        <p class="success--text display-1 font-weight-black">
          {{ $t('news.subtitle') }}
        </p>
        <p>
          {{ $t('news.text') }}
        </p>
        <v-btn
          rounded
          color="secondary"
          :href="this.$t('solidary.button.route')"
          class="white--text"
        >
          {{ $t('solidary.button.label') }}
        </v-btn>
      </v-col>
      <v-col cols="4">
        <v-img
          :src="imageLink + 'solidaire.png'"
          class="align-self-center"
          cover
        />
      </v-col>
    </v-row>

    <!-- features -->
    <div :style="'background-image:url(\''+$t('urlBackground2')+'\');background-position-y:bottom;background-size:100% 100%;background-repeat:no-repeat;'">
      <v-row
        align="center"
        class="mt-2"
        justify="center"
      >
        <v-col
          cols="12"
          xl="6"
          lg="8"
          md="12"
        >
          <m-features
            :title="$t('title')"
            :subtitle="$t('subtitle')"
            :items="items"
            class="mt-10"
          />
        </v-col>
      </v-row>
    </div>
    <!-- end features -->
    <!-- Event -->
    <v-row
      dense
      justify="center"
      class="mt-10"
    >
      <v-col
        cols="12"
        xl="6"
        lg="8"
        md="12"
      >
        <MEvents />
      </v-col>
    </v-row>
    <!-- end Event -->

    <!-- cooperative -->
    <div
      :style="'background-image:url(\''+$t('urlBackground3')+'\');background-size:cover;background-position-y:-50em;'"
      class="mt-n8"
    >
      <v-row
        align="center"
        class="mt-4"
        justify="center"
      >
        <v-col
          cols="12"
          xl="6"
          lg="8"
          md="12"
          class="px-14"
        >
          <m-cooperative />
        </v-col>
      </v-row>
      <!-- end cooperative -->
      <!-- rssArticle -->
      <v-row
        align="center"
        class="mt-4"
        justify="center"
      >
        <v-col
          cols="12"
          xl="6"
          lg="8"
          md="12"
          class="px-14"
        >
          <p class="success--text text-h4 font-weight-black mt-16">
            Quoi de neuf chez Mobicoop ?
          </p>

          <m-rss-articles
            :articles="article"
          />
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/home/HomeContent/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/home/HomeContent/";
import MFeatures from "@components/utilities/MFeatures";
import MEvents from "@components/utilities/event/MEvents";
import MCooperative from "@components/utilities/cooperative/MCooperative";
import MRssArticles from "@components/utilities/rssArticle/MRssArticles";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  components: {
    MFeatures,
    MEvents,
    MCooperative,
    MRssArticles
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props: {
    urlMobile: {
      type: String,
      default: null
    },
    article: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      mobileUrl: this.urlMobile,
      imageLink: "/images/pages/home/",
      items:this.$t("items")
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

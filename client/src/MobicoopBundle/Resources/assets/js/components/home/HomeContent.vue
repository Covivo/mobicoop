<template>
  <v-container
    fluid
    pa-0
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
        <m-news />
      </v-col>
    </v-row>
    <!-- features -->
    <div :style="'background-image:url(\''+$t('urlBackground2')+'\');background-position-y:bottom;background-size:100% 100%;background-repeat:no-repeat;alt=\'\';'">
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
        <!-- events -->
        <v-row justify="center">
          <v-col
            cols="4"
            class="text-left"
          >
            <h2 class="primary--text font-weight-bold">
              {{ $t('events.title') }}
            </h2>
            <v-btn
              rounded
              color="secondary"
              :href="this.$t('events.button1.route')"
              class="white--text mt-2"
            >
              {{ $t('events.button1.label') }}
            </v-btn>
            <v-btn
              rounded
              color="secondary"
              :href="this.$t('events.button2.route')"
              class="white--text mt-4"
            >
              {{ $t('events.button2.label') }}
            </v-btn>
            <v-img
              class="mt-6"
              :src="imageLink + 'van_evenement.svg'"
              :alt="$t('events.image.van')"
            />
          </v-col>
          <v-col
            cols="6"
            xl="8"
            lg="6"
            md="6"
          >
            <MEvents />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <!-- end Event -->

    <!-- journeyCityToCity -->
    <v-row
      class="secondary"
      justify="center"
      align="center"
    >
      <v-col
        cols="12"
        xl="6"
        lg="8"
        md="12"
      >
        <JourneyCityToCity />
      </v-col>
    </v-row>
    <!-- end journeyCityToCity -->

    <!-- cooperative -->
    <div
      :style="'background-image:url(\''+$t('urlBackground3')+'\');background-size:cover;background-position-y:right;'"
    >
      <v-row
        align="center"
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
          <h3 class="primary--text  font-weight-bold mt-16">
            {{ $t('articles.title') }}
          </h3>

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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/home/HomeContent/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/home/HomeContent/";
import MNews from "@components/utilities/news/MNews";
import MFeatures from "@components/utilities/MFeatures";
import MEvents from "@components/utilities/event/MEvents";
import JourneyCityToCity from "@components/journey/JourneyCityToCity";
import MCooperative from "@components/utilities/cooperative/MCooperative";
import MRssArticles from "@components/utilities/rssArticle/MRssArticles";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  components: {
    MNews,
    MFeatures,
    MEvents,
    JourneyCityToCity,
    MCooperative,
    MRssArticles
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
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
    },
    userId: {
      type: Number,
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
h2{
  font-size: 2rem;
  line-height: 1.2;
}

h3{
  font-size: 1.8rem;
  line-height: 1.2;
}
</style>

<template>
  <v-container fluid>
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
        <!-- events -->
        <v-row justify="center">
          <v-col
            cols="4"
            class="text-left"
          >
            <p class="primary--text display-1 font-weight-black">
              {{ $t('events.title') }}
            </p>
            <v-btn
              rounded
              color="secondary"
              :href="this.$t('events.button1.route')"
              class="white--text"
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
            <img
              class="mt-6"
              :src="imageLink + 'van_evenement.svg'"
            >
          </v-col>
          <v-col cols="6">
            <MEvents />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col
        class="text-center mt-16"
      >
        <p class="primary--text display-1 font-weight-black">
          {{ $t('privateEvent.title') }}
        </p>
        <p class="primary--text headline font-italic mt-n4">
          {{ $t('privateEvent.subtitle') }}
        </p>
        <p>
          {{ $t('privateEvent.text') }}
        </p>
        <v-btn
          rounded
          color="secondary"
          :href="this.$t('privateEvent.button.route')"
          class="white--text mt-4"
        >
          {{ $t('privateEvent.button.label') }}
        </v-btn>
        <p class="mt-4">
          <a
            :href="this.$t('privateEvent.coviEvent.link')"
          >
            {{ $t('privateEvent.coviEvent.text') }}
          </a>
        </p>
      </v-col>
    </v-row>
    <!-- end Event -->

    <!-- cityToCity -->
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        cols="12"
        xl="6"
        lg="9"
        md="12"
        class="px-14"
      >
        <JourneyCityToCity />
      </v-col>
    </v-row>
    <!-- end cityToCity -->

    <v-row>
      <v-col>
        <v-sheet
          color="secondary"
          flat
          height="136"
          width="2000"
        />
      </v-col>
    </v-row>


    <!-- cooperative -->
    <div
      :style="'background-image:url(\''+$t('urlBackground3')+'\');background-size:cover;background-position-y:right;'"
      class="mt-n8"
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
          <p class="primary--text text-h4 font-weight-black mt-16">
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
import MNews from "@components/utilities/news/MNews";
import MFeatures from "@components/utilities/MFeatures";
import MEvents from "@components/utilities/event/MEvents";
import JourneyCityToCity from "@components/journey/JourneyCityToCity";
import MCooperative from "@components/utilities/cooperative/MCooperative";
import MRssArticles from "@components/utilities/rssArticle/MRssArticles";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

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

<template>
  <v-container>
    <v-row
      dense
      justify="center"
      class="mt-10"
    >
      <v-col
        v-if="solidaryDisplay"
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
  </v-container>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/home/HomeContent/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/home/HomeContent/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props: {
    solidaryDisplay: {
      type: Boolean,
      default: false
    },

    article: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      imageLink: "/images/pages/home/",
      items:this.$t("items")
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

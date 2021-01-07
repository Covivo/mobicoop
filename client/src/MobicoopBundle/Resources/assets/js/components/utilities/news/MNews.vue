
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
  </v-container>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/utilities/MNews/";

let MessagesMergedEn = merge(messages_en);
let MessagesMergedFr = merge(messages_fr);

export default {
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
    }
  },
  data () {
    return {
      imageLink: "/images/pages/home/",
      mobileUrl: this.urlMobile
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

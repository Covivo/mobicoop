
<template>
  <v-container fluid>
    <v-row
      dense
      justify="center"
      class="mt-10"
    >
      <v-col
        cols="7"
        class="text-right align-self-center"
      >
        <h3 class="primary--text font-italic">
          {{ $t('news.title') }}
        </h3>
        <h3 class="primary--text font-weight-bold">
          {{ $t('news.subtitle') }}
        </h3>
        <p>
          {{ $t('news.text') }}
        </p>
        <v-btn
          rounded
          color="secondary"
          :href="this.$t('news.button.route')"
          class="white--text"
          target="_blank"
        >
          {{ $t('news.button.label') }}
        </v-btn>
      </v-col>
      <v-spacer />


      <v-col 
        cols="4"           
        class="align-self-center"
      >
        <v-img
          :src="imageLink + '210112_MBP_VisuelSite.png'"
          cover
        />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MNews/";

let MessagesMergedEn = merge(messages_en);
let MessagesMergedFr = merge(messages_fr);
let MessagesMergedEu = merge(messages_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
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
h3{
    font-size: 1.8rem;
    line-height: 1.2;
  }
</style>

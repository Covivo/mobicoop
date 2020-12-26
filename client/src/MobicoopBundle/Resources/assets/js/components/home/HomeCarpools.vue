<template>
  <v-row
    dense
    justify="center"
  >
    <!-- next carpools -->
    <v-col
      cols="12"
      class="mt-16 white--text"
    >
      <v-card
        color="secondary"
        flat
      >
        <v-card-title>
          Les prochains trajets
        </v-card-title>
      </v-card>
    </v-col>
    <!-- end next carpools -->
  </v-row>
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
    eventDisplay: {
      type: Boolean,
      default: false
    },
    communityDisplay: {
      type: Boolean,
      default: false
    },
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

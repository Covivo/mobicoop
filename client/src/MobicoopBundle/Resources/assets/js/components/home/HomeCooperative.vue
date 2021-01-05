<template>
  <v-row
    dense
    justify="center"
    class="mt-6"
  >
    <!-- cooperative -->
    <v-col
      cols="5"
      class="text-left "
    >
      <span class="success--text display-1 font-weight-black">
        {{ $t('cooperative.title') }}
      </span>
      <span v-html="$t('cooperative.text')" />
    </v-col>
    <v-spacer />
    <v-col cols="6">
      <v-img
        :src="imageLink + 'cooperative.png'"
        class="align-self-center"
        contain
      />
    </v-col>
    <v-col
      cols="12"
      class="mt-8"
    >
      <v-btn
        rounded
        color="secondary"
        :href="this.$t('cooperative.button1.route')"
        class="white--text "
        max-width="400px"
      >
        {{ $t('cooperative.button1.label') }}
      </v-btn>
      <v-btn
        rounded
        color="secondary"
        :href="this.$t('cooperative.button2.route')"
        class="white--text"
        max-width="400px"
      >
        {{ $t('cooperative.button2.label') }}
      </v-btn>
    </v-col>
    <!-- end cooperative -->
  </v-row>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/home/HomeCooperative/";

let MessagesMergedEn = merge(messages_en);
let MessagesMergedFr = merge(messages_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  components: {
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

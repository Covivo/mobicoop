<template>
  <v-container fluid>
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
        <h3 class="primary--text font-weight-bold">
          {{ $t('cooperative.title') }}
        </h3><br>
        <p
          v-html="$t('cooperative.text')"
        />
      </v-col>
      <v-spacer />
      <v-col cols="6">
        <v-img
          :src="imageLink + 'cooperative.png'"
          class="align-self-center"
          :alt="$t('cooperative.image')"
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
          target="_blank"
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
  </v-container>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MCooperative/";

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
  h3{
    font-size: 1.8rem;
    line-height: 1.2;
  }
</style>


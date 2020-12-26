<template>
  <v-container>
    <v-row
      dense
      justify="center"
    >
      <!-- events -->
      <v-row class="mt-16 pt-16">
        <v-col
          cols="4"
          class="text-left mt-16 pt-16"
        >
          <p class="success--text text-h4 font-weight-black">
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
        <v-col cols="8" />
      </v-row>
      <v-row justify="center">
        <v-col
          cols="6"
          class="text-center mt-16"
        >
          <p class="success--text text-h4 font-weight-black">
            {{ $t('privateEvent.title') }}
          </p>
          <p class="success--text text-h6 font-italic mt-n4">
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
      <!-- end events -->
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

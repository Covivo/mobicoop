
<template>
  <v-container fluid>
    <v-row
      dense
      justify="center"
      class="mt-10"
    >
      <v-col
        cols="6"
        class="text-right align-self-center"
      >
        <h2 class="primary--text font-italic">
          {{ $t('news.title') }}
        </h2>
        <h3 class="primary--text font-weight-bold">
          {{ $t('news.subtitle') }}
        </h3>
        <p>
          {{ $t('news.text') }}
        </p>

        <!-- <v-dialog
          v-model="dialog"
          cover
          width="450"
        >
          <template v-slot:activator="{ on, attrs }"> -->
        <v-btn
          color="primary"
          class="white--text"
          :href="$t('news.button.link')"
        >
          {{ $t('news.button.label') }}
        </v-btn>
        <!-- </template>

          <v-card
            style="overflow:hidden"
          >
            <v-row dense>
              <v-col class="text-right">
                <v-btn
                  class="text"
                  icon
                  @click="dialog = false"
                >
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </v-col>
            </v-row>
            <v-row class="pa-1">
              <v-col class="pa-0">
                <v-card-title class="font-weight-bold justify-center pa-0">
                  {{ $t('news.popIn.ios') }}
                </v-card-title>
                <v-img
                  :src="imageLink + 'Ios_Unitag_QRCode_1652860756352.png'"
                  cover
                  :alt="$t('news.imageAlt')"
                />
              </v-col>

              <v-divider
                vertical
              />
              <v-col class="pa-0">
                <v-card-title class="font-weight-bold justify-center pa-0">
                  {{ $t('news.popIn.android') }}
                </v-card-title>
                <v-img
                  :src="imageLink + 'Android_Unitag_QRCode_1652860710477.png'"
                  cover
                  :alt="$t('news.imageAlt')"
                />
              </v-col>
            </v-row>
          </v-card>
        </v-dialog> -->
      </v-col>
      <v-spacer />

      <v-col
        cols="5"
        class="align-self-center"
      >
        <v-img
          :src="imageLink + 'Mobicoop_Actu_CEE.png'"
          cover
          :alt="$t('news.imageAlt')"
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
      mobileUrl: this.urlMobile,
      dialog: false
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

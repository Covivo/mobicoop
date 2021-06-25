<template>
  <v-container fluid>
    <v-row>
      <v-col
        class="text-left mt-16"
      >
        <h3
          class="primary--text font-weight-bold"
        >
          {{ $t('title') }}
        </h3>       
      </v-col>
      <v-spacer />
    </v-row>
    <v-row justify="center">
      <MFeaturesItem
        v-for="(item, index) in items"
        :key="index"
        :title="item.title"
        :text="item.text"
      />
    </v-row>
  </v-container>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MFeatures/";

let MessagesMergedEn = merge(messages_en);
let MessagesMergedFr = merge(messages_fr);
let MessagesMergedEu = merge(messages_eu);


import MFeaturesItem from "@components/utilities/MFeaturesItem";
export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  components: {
    MFeaturesItem
  },
  props: {
    title: {
      type: String,
      default: ""
    },
    text: {
      type: String,
      default: ""
    },
    items:{
      type: Array,
      default: null
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

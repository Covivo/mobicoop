<template>
  <div>
    <v-row>
      <v-col
        class="text-left mt-16 success--text display-1 font-weight-bold"
        cols="6"
      >
        {{ $t('title') }}        
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
  </div>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/utilities/MFeatures/";

let MessagesMergedEn = merge(messages_en);
let MessagesMergedFr = merge(messages_fr);

import MFeaturesItem from "@components/utilities/MFeaturesItem";
export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
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
</style>
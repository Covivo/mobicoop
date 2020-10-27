<template>
  <v-container>
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        :cols="nbCols"
      >
        <h1 class="text-h4 primary--text text-center font-weight-bold">
          {{ $t('title') }}
        </h1>
      </v-col>
    </v-row>
    <v-row  
      v-if="items.length>0"
      align="center"
      justify="center"
    >
      <v-col
        :cols="nbCols"
      >
        <h2 class="font-weight-bold">
          {{ $t('subtitle') }}
        </h2>
        <p>{{ $t('intro') }}</p>
      </v-col>
    </v-row>

    <v-row 
      v-if="items.length>0"
      align="center"
      justify="center"
    >
      <v-col :cols="nbCols">
        <v-row>
          <v-col
            v-for="(item, i) in items"
            :key="i"
            cols="4"
          >
            <ToolBoxItem
              :image="item.image"
              :link="item.link"
              :text-link="item.textLink"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row
      align="center"
      justify="center"
    >
      <v-col :cols="nbCols">
        <h2>{{ $t('widget.title') }}</h2>
        <p>
          <a
            :href="$t('widget.link')"
            title=""
          >{{ $t('widget.textLink') }}</a> {{ $t('widget.text') }}.
        </p>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import {messages_fr, messages_en} from "@translations/components/utilities/ToolBox/ToolBox/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/utilities/ToolBox/ToolBox/";
import ToolBoxItem from "@components/utilities/ToolBox/ToolBoxItem";
let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
export default {
  components:{
    ToolBoxItem
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    },
  },
  props: {},
  data() {
    return {
      items:this.$t('items'),
      nbCols:10
    }
  }
};
</script>

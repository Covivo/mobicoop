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
      v-if="itemsVisual.length>0"
      align="center"
      justify="center"
    >
      <v-col
        :cols="nbCols"
      >
        <h2 class="font-weight-bold">
          {{ $t('subtitle') }}
        </h2>
      </v-col>
    </v-row>

    <!-- Visuals -->

    <v-row
      v-if="itemsVisual.length>0"
      align="center"
      justify="center"
    >
      <v-col
        :cols="nbCols"
      >
        <h2 class="font-weight-bold">
          {{ $t('subtitleVisual') }}
        </h2>
      </v-col>
    </v-row>

    <v-row
      v-if="itemsVisual.length>0"
      align="center"
      justify="center"
    >
      <v-col :cols="nbCols">
        <v-row>
          <v-col
            v-for="(item, i) in itemsVisual"
            :key="i"
            cols="4"
            lg="4"
            md="8"
          >
            <ToolBoxItem
              :title="item.title"
              :subtitle="item.subtitle"
              :link-subtitle="item.linkSubtitle"
              :image="item.image"
              :text-download="item.textDownload"
              :download-link="item.downloadLink"
              :personalized-text="item.personalizedText"
              :personalized-text-link="item.personalizedTextLink"
              :link1="item.link1"
              :link2="item.link2"
              :link3="item.link3"
              :text-logo1="item.textLogo1"
              :text-logo2="item.textLogo2"
              :text-logo3="item.textLogo3"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Animation -->
    <v-row
      v-if="itemsAnimation.length>0"
      align="center"
      justify="center"
    >
      <v-col
        :cols="nbCols"
      >
        <h2 class="font-weight-bold">
          {{ $t('subtitleAnimation') }}
        </h2>
      </v-col>
    </v-row>

    <v-row
      v-if="itemsAnimation.length>0"
      align="center"
      justify="center"
    >
      <v-col :cols="nbCols">
        <v-row>
          <v-col
            v-for="(item, i) in itemsAnimation"
            :key="i"
            cols="4"
            lg="4"
            md="8"
          >
            <ToolBoxItem
              :title="item.title"
              :subtitle="item.subtitle"
              :link-subtitle="item.linkSubtitle"
              :image="item.image"
              :text-download="item.textDownload"
              :download-link="item.downloadLink"
              :personalized-text="item.personalizedText"
              :personalized-text-link="item.personalizedTextLink"
              :link1="item.link1"
              :link2="item.link2"
              :link3="item.link3"
              :text-logo1="item.textLogo1"
              :text-logo2="item.textLogo2"
              :text-logo3="item.textLogo3"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Widget -->
    <v-row
      align="center"
      justify="center"
    >
      <v-col :cols="nbCols">
        <h2>{{ $t('widget.title') }}</h2>
        <platform-get-widget />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/ToolBox/ToolBox/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/ToolBox/ToolBox/";
import ToolBoxItem from "@components/utilities/ToolBox/ToolBoxItem";
import PlatformGetWidget from '@components/utilities/platformWidget/PlatformGetWidget';

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);
export default {
  components:{
    PlatformGetWidget,
    ToolBoxItem
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    },
  },
  props: {},
  data() {
    return {
      itemsVisual: this.$t('itemsVisual'),
      itemsAnimation: this.$t('itemsAnimation'),
      nbCols:10
    }
  }
};
</script>

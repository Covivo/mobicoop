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
import Translations from "@translations/components/article/ToolBox.json";
import TranslationsClient from "@clientTranslations/components/article/ToolBox.json";
import ToolBoxItem from "@components/utilities/ToolBox/ToolBoxItem";
let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components:{
    ToolBoxItem
  },
  i18n: {
    messages: TranslationsMerged,
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

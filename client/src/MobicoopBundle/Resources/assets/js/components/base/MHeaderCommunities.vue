<template>
  <v-menu
    v-if="items.length > 0"
    offset-y
    open-on-hover
  >
    <template v-slot:activator="{ on }">
      <v-btn
        rounded
        text
        :class="textColorClass"
        v-on="on"
      >
        {{ $t('title') }}
        <v-icon>mdi-chevron-down</v-icon>
      </v-btn>
    </template>
    <v-list>
      <v-list-item
        v-for="(item, index) in items"
        :key="index"
      >
        <a
          :href="$t('urlCommunityDetails',{id:item.id,urlKey:item.urlKey})"
          :alt="item.name"
          :class="linksColorClass"
        ><v-list-item-title>{{ item.name }}</v-list-item-title></a>
      </v-list-item>
      <v-divider v-if="items.length>0" />
      <v-list-item>
        <a
          :href="$t('urlCommunityList')"
          :alt="$t('availableCommunities')"
          :class="linksColorClass"
        >{{ $t("availableCommunities") }}</a>
      </v-list-item>
    </v-list>
  </v-menu>
</template>
<script>
import { merge } from "lodash";
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/base/MHeaderCommunities/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/base/MHeaderCommunities/"

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  props:{
    userId:{
      type: Number,
      default:0
    },
    textColorClass: {
      type: String,
      default: ""
    },
    linksColorClass: {
      type: String,
      default: ""
    } 
  },
  data(){
    return {
      items: []
    }
  },
  mounted(){
    let params = {
      'userId':this.userId,
    }
    maxios.post(this.$t("getCommunities"), params)
      .then(res => {
        this.items = res.data;
      })
      .catch(function (error) {
        console.error(error);
      });
  },
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
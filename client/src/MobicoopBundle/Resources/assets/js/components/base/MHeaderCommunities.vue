<template>
  <v-menu
    offset-y
    open-on-hover
  >
    <template v-slot:activator="{ on }">
      <v-btn
        rounded
        text
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
          :href="$t('urlCommunityDetails',{id:item.id})"
          :alt="item.name"
        ><v-list-item-title>{{ item.name }}</v-list-item-title></a>
      </v-list-item>
      <v-divider v-if="items.length>0" />
      <v-list-item>
        <a
          :href="$t('urlCommunityList')"
          :alt="$t('availableCommunities')"
        >{{ $t("availableCommunities") }}</a>
      </v-list-item>
    </v-list>
  </v-menu>
</template>
<script>
import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/base/MHeaderCommunities.json";
import TranslationsClient from "@clientTranslations/components/base/MHeaderCommunities.json";
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  props:{
    userId:{
      type: Number,
      default:0
    }
  },
  data(){
    return {
      items: []
    }
  },
  mounted(){
    let params = {
      'userId':this.userId
    }
    axios.post(this.$t("getCommunities"), params)
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
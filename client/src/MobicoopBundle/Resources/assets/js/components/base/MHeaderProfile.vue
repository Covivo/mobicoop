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
        <v-avatar
          class="mr-1"
          width="36px"
          height="36px"
        >
          <img
            v-if="avatar"
            :src="avatar"
          >
          <v-icon
            v-else
          >
            mdi-account-circle
          </v-icon>
        </v-avatar>
        {{ shortFamilyName }}
        <v-icon>mdi-chevron-down</v-icon>
      </v-btn>
    </template>
    <v-list>
      <v-list-item
        v-for="(item, index) in items"
        :key="index"
      >
        <a
          v-if="item.title"
          :href="item.url"
          :alt="item.title"
        ><v-list-item-title>{{ item.title }}</v-list-item-title></a>
        <v-divider v-else />
      </v-list-item>
    </v-list>
  </v-menu>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/base/MHeaderProfile.json";
import TranslationsClient from "@clientTranslations/components/base/MHeaderProfile.json";
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    avatar:{
      type: String,
      default:null
    },
    shortFamilyName:{
      type: String,
      default:"Profil"
    }
  },
  data(){
    return {
      items: [
        { title: this.$t('myAds.label'), url: this.$t('myAds.route') },
        { title: this.$t('myAcceptedCarpools.label'), url: this.$t('myAcceptedCarpools.route') },
        { title: this.$t('myProfile.label'), url: this.$t('myProfile.route') },
        {},
        { title: this.$t('logOut.label'), url: this.$t('logOut.route') }
      ],
    }
  },
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
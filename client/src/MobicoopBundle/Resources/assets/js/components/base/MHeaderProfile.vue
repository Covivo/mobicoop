<template>
  <v-menu
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
        <v-avatar
          class="mr-1"
          width="36px"
          height="36px"
        >
          <img
            v-if="avatar"
            :src="avatar"
            style="width:auto;"
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
          :class="linksColorClass"
        ><v-list-item-title>{{ item.title }}</v-list-item-title></a>
        <v-divider v-else />
      </v-list-item>
    </v-list>
  </v-menu>
</template>
<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/base/MHeaderProfile/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/base/MHeaderProfile/"

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
  props: {
    avatar:{
      type: String,
      default:null
    },
    shortFamilyName:{
      type: String,
      default:"Profil"
    },
    textColorClass: {
      type: String,
      default: ""
    },
    linksColorClass: {
      type: String,
      default: ""
    },
    showReviews: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return {
      
    }
  },
  computed:{
    items(){

      let items = [
        { title: this.$t('myAds.label'), url: this.$t('myAds.route') },
        { title: this.$t('myAcceptedCarpools.label'), url: this.$t('myAcceptedCarpools.route') },
        { title: this.$t('myProfile.label'), url: this.$t('myProfile.route') },
      ];

      if(this.showReviews){
        items.push({ title: this.$t('reviews.label'), url: this.$t('reviews.route') });
      }

      items.push({});
      items.push({ title: this.$t('logOut.label'), url: this.$t('logOut.route') });

      return items;
    }
  }
}
</script>
<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
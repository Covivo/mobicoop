<template>
  <v-container
    fluid     
  >
    <v-snackbar
      v-model="snackbar"
      :color="(alert.type === 'error')?'error':'success'"
      top
    >
      {{ alert.message }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
    <v-row justify="center">
      <v-col>
        <v-tabs
          centered
          grow
        >
          <v-tab>{{ $t('ongoing') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.ongoing">
              <v-row
                v-for="ad in localAds.ongoing"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Ad
                    :ad="ad"
                    @ad-deleted="deleteAd"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
          <v-tab>{{ $t('archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.archived">
              <v-row
                v-for="ad in localAds.archived"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Ad
                    :ad="ad"
                    :is-archived="true"
                    @ad-deleted="deleteAd"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import { merge, omit } from "lodash";
import {messages_fr, messages_en} from "@translations/components/user/profile/ad/Ads/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/user/profile/ad/Ads/";
import Ad from "@components/user/profile/ad/Ad.vue";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  components: {
    Ad
  },
  props: {
    ads: {
      type: Object,
      default: () => {}
    }
  },
  data(){
    return {
      localAds: this.ads,
      snackbar: false,
      alert: {
        type: "success",
        message: ""
      }
    }
  },
  methods: {
    deleteAd(isArchived, id, message) {
      let type = isArchived ? "archived" : "ongoing";
      this.localAds[type] = omit(this.localAds[type], id);
      this.alert.message = message;
      this.snackbar = true;
    }
  }
}
</script>
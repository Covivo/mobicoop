<template>
  <v-container
    fluid     
  >
    <v-row justify="center">
      <v-col>
        <v-tabs
          centered
          grow
        >
          <v-tab>{{ $t('ads.ongoing') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.ongoing">
              <v-row
                v-for="ad in localAds.ongoing"
                :key="ad.outward.id"
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
          <v-tab>{{ $t('ads.archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.archived">
              <v-row
                v-for="ad in localAds.archived"
                :key="ad.outward.id"
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
import Translations from "@translations/components/user/profile/ad/MyAds.js";
import TranslationsClient from "@clientTranslations/components/user/profile/ad/MyAds.js";

import Ad from "@components/user/profile/ad/Ad.vue";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
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
      localAds: this.ads
    }
  },
  methods: {
    deleteAd(isArchived, id) {
      let type = isArchived ? "archived" : "ongoing";
      this.localAds[type] = omit(this.localAds[type], id);
    }
  }
}
</script>
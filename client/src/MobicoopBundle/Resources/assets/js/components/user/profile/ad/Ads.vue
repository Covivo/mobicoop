<template>
  <v-container
    fluid     
  >
    <!-- Alert message -->
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

    <!-- Ad list -->
    <v-row justify="center">
      <v-col>
        <v-tabs
          centered
          grow
        >
          <!-- Active ads -->
          <v-tab>{{ $t('active') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.active">
              <v-row
                v-for="ad in localAds.active"
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

          <!-- Archived ads -->
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

import { omit } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ad/Ads/";
import Ad from "@components/user/profile/ad/Ad.vue";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
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
  watch: {
    ads() {
      this.localAds = this.ads;
    }
  },
  methods: {
    deleteAd(isArchived, id, message) {
      let type = isArchived ? "archived" : "active";
      this.localAds[type] = omit(this.localAds[type], id);
      this.alert.message = message;
      this.snackbar = true;
    }
  }
}
</script>
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
              <div
                v-if="punctualAds.length"
                class="mt-10"
              >
                <v-row
                  v-for="ad in punctualAds"
                  :key="ad.id"
                >
                  <v-col cols="12">
                    <Ad
                      :ad="ad"
                      @ad-deleted="deleteAd"
                    />
                  </v-col>
                </v-row>
              </div>
              <div
                v-if="regularAds.length"
                class="mt-5"
              >
                <h2 class="h4 secondary--text">
                  {{ $t('regular-ads') }}
                </h2>
                <v-row
                  v-for="ad in regularAds"
                  :key="ad.id"
                >
                  <v-col cols="12">
                    <Ad
                      :ad="ad"
                      @ad-deleted="deleteAd"
                    />
                  </v-col>
                </v-row>
              </div>
              <no-ad
                v-if="!regularAds.length && !punctualAds.length"
                active
              />
            </v-container>
            <v-skeleton-loader
              v-else
              color="secondary"
              type="article"
              class="ma-10"
            />
          </v-tab-item>

          <!-- Archived ads -->
          <v-tab>{{ $t('archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.archived">
              <div v-if="localAds.archived.length">
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
              </div>
              <no-ad v-else />
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
import NoAd from "@components/user/profile/ad/NoAd.vue";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu': messages_eu
    }
  },
  components: {
    Ad,
    NoAd
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
      regularAds: [],
      punctualAds: [],
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
      if (this.ads && this.ads.active && this.ads.active.length) {
        this.regularAds = [...this.ads.active];
        this.regularAds = this.regularAds
          .filter(ad => 2 === ad.frequency);

        this.punctualAds = [...this.ads.active];
        this.punctualAds = this.punctualAds
          .filter(ad => 1 === ad.frequency)
          .sort((a, b) => {
            return new Date(`${a.outwardDate} ${a.outwardTime}`) - new Date(`${b.outwardDate} ${b.outwardTime}`);
          });
      }
    }
  },
  methods: {
    deleteAd(isArchived, id, message) {
      let type = isArchived ? "archived" : "active";
      this.localAds[type] = omit(this.localAds[type], id);
      this.$emit('ad-deleted')
      setTimeout(() => {
        this.alert.message = message;
        this.snackbar = true;
      }, 2500);
    }
  }
}
</script>

<style lang="scss" scoped>
h2 {
  margin-bottom: 10px;

  border-bottom: 1px dotted silver;
}
</style>

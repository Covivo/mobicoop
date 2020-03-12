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
          <v-tab>{{ $t('carpools.ongoing') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.ongoing">
              <v-row
                v-for="ad in localAds.ongoing"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :user="user"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
          <v-tab>{{ $t('carpools.archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.archived">
              <v-row
                v-for="ad in localAds.archived"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :is-archived="true"
                    :user="user"
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
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/carpool/AcceptedCarpools.js";
import TranslationsClient from "@clientTranslations/components/user/profile/carpool/AcceptedCarpools.js";

import Carpool from "@components/user/profile/carpool/Carpool.vue";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
  },
  components: {
    Carpool
  },
  props: {
    acceptedCarpools: {
      type: Object,
      default: () => {}
    },
    user: {
      type: Object,
      default: null
    }
  },
  data(){
    return {
      localAds: this.acceptedCarpools
    }
  }
}
</script>

<style scoped>

</style>
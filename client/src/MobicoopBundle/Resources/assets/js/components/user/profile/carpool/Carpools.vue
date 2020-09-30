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
              <v-row>
                <v-col
                  cols="8"
                  class="font-weight-bold text-h5"
                >
                  Besoin d’une preuve de covoiturage ?
                </v-col>
                <v-col                   
                  cols="8"
                  class="font-italic text-caption"
                >
                  En cliquant sur «Exporter» vous pouvez télécharger tous vos covoiturages au format pdf ou csv.
                </v-col>
                <v-btn
                  color="secondary"
                  rounded
                  href="/utilisateur/profil/modifier/mes-covoiturages-acceptes/export"
                  width="175px"
                >
                  Exporter
                </v-btn>
              </v-row>
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
import Translations from "@translations/components/user/profile/carpool/AcceptedCarpools.js";

import Carpool from "@components/user/profile/carpool/Carpool.vue";

export default {
  i18n: {
    messages: Translations,
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
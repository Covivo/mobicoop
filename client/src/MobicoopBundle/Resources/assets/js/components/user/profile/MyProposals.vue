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
          <v-tab>{{ $t('proposals.ongoing') }}</v-tab>
          <v-tab-item>
            <v-container
              fluid
            >
              <v-card
                v-for="proposal in proposals"
                :key="proposal.outward.id"
              >
                {{ proposal.outward.comment }}
              </v-card>
            </v-container>
          </v-tab-item>
          <v-tab>{{ $t('proposals.archived') }}</v-tab>
          <v-tab-item>
            <v-container
              fluid
            >
              <p>annonces archivées</p>
            </v-container>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/Profile.json";
import TranslationsClient from "@clientTranslations/components/user/profile/Profile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    proposals: {
      type: Array,
      default: () => []
    }
  },
  data(){
    return {
      
    }
  }
}
</script>
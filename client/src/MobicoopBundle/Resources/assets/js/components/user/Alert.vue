<template>
  <v-card>
    <v-row no-gutters>
      <v-col class="cols-12">
        <p class="mb-0 mt-2">
          {{ $t("alerts."+alert) }}
        </p>
      </v-col>
    </v-row>
    <v-row>
      <v-col class="cols-12 text-left">
        <v-row
          v-for="(media, index) in medium"
          :key="index"
          no-gutters
          class="mb-1 text-center"
        >
          <v-col class="cols-9">
            {{ $t("medium.media"+media.medium) }}
          </v-col>
          <v-col class="cols-1">
            <v-switch
              v-model="media.active"
              inset
              hide-details
              class="mt-0"
              color="primary"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-card>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/Alert.json";
import TranslationsClient from "@clientTranslations/components/user/Alert.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    alert:{
      type: String,
      default:null
    },
    medium:{
      type: Object,
      default:null
    }
  },
  data(){
    return{
      dataMedium:this.medium
    }
  }
}
</script>
<template>
  <v-card
    :height="cardHeight"
    flat
  >
    <v-row no-gutters>
      <v-col class="cols-12 ma-2 text-center">
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
              color="secondary"
              @change="emit(media.id,media.active)"
            />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-card>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/Alert.json";
import TranslationsClient from "@clientTranslations/components/user/profile/Alert.json";

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
      type: Array,
      default:null
    }
  },
  data(){
    return{
      dataMedium: this.medium,
      cardHeight: '100%'
    }
  },
  methods:{
    emit(id,active){
      this.$emit("changeAlert",{id:id,active:active})
    }
  }
}
</script>
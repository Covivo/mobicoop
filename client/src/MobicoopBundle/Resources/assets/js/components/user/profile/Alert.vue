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

    <!--Confirmation Popup-->
    <v-dialog
      v-model="dialog"
      max-width="495"
    >
      <v-card>
        <v-card-title class="headline">
          {{ $t('popup.title') }}
        </v-card-title>
        <v-card-text
          v-html="$t('popup.content', {
            'alert': $t('alerts.'+alert)
          })"
        />
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="secondary darken-1"
            text
            @click="dialog=false"
          >
            {{ $t('ui.common.ok') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
      cardHeight: '100%',
      dialog: false,
    }
  },
  methods:{
    emit(id,active){
      this.$emit("changeAlert",{id:id,active:active});

      let alertIsFullyInactive = true;

      for (const i in this.medium) {
        if(this.medium[i].active) {
          alertIsFullyInactive = false;
          break;
        }
      }
      this.dialog = alertIsFullyInactive;
    },
  }
}
</script>

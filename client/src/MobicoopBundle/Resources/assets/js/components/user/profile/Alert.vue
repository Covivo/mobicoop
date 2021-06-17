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
        <v-card-title class="text-h5">
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
            {{ $t('ok') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>
<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/Alert/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/profile/Alert/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);
export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
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

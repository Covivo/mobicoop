<template>
  <v-dialog
    v-model="carpoolingIncentiveDialog"
    hide-overlay
    persistent
    width="850"
    content-class="rounded-xl elevation-5"
  >
    <v-card>
      <v-card-text class="pa-0 carpool-incentive-dialog-text">
        <v-row>
          <v-col
            cols="5"
            class="pa-0 carpool-incentive-dialog-image"
          />
          <v-col
            cols="7"
            class="px-10 pb-10"
          >
            <p class="d-flex flex-row-reverse">
              <v-btn
                icon
                @click="carpoolingIncentiveDialog = false"
              >
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </p>
            <p class="mb-0 font-weight-bold">
              {{ $t('title') }}
            </p>
            <p v-html="$t('text')" />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MDialog/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/MDialog/";


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
    },
  },
  data() {
    return {
      carpoolingIncentiveDialog: true,
    };
  }
}
</script>

<style lang="scss" scoped>
.carpool-incentive-dialog-image {
  background-image: url("/images/pages/home/illus-CEE.png");
  background-position: center;
  background-size: cover;
}
.carpool-incentive-dialog-text {
  overflow: hidden;
  font-size: 1rem;
  line-height: 1.75rem;
}
</style>

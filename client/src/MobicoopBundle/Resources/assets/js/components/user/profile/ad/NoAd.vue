<template>
  <div>
    <v-row>
      <v-col>
        <v-icon color="secondary">
          mdi-information
        </v-icon>
        <span class="pl-1">{{ text }}</span>
      </v-col>
    </v-row>
    <v-row v-if="active">
      <v-col class="text-center">
        <v-btn
          class="my-5"
          :href="$t('buttons.shareAnAd.route')"
          outlined
          rounded
          :aria-label="$t('buttons.shareAnAd.label')"
        >
          {{ $t("buttons.shareAnAd.label") }}
        </v-btn>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ad/Ads/";
import {messages_en as messages_client_en, messages_fr as messages_client_fr, messages_eu as messages_client_eu, messages_nl as messages_client_nl} from "@translations/components/carpool/search/Search/";

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
    active: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    text() {
      return this.active ? this.$t('no-active-ad') : this.$t('no-archived-ad');
    }
  },
};
</script>

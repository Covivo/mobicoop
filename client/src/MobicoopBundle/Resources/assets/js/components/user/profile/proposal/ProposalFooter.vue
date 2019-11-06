<template>
  <v-container fluid>
    <v-row>
      <v-col cols="3">
        <span v-if="seats && seats > 0">{{ seats }}&nbsp;{{ seats > 1 ? $t('seat.plural') : $t('seat.singular') }}</span>
      </v-col>
      <v-col cols="3">
        <span v-if="price && price > '0'">{{ price }} €</span>
      </v-col>
      <v-col
        cols="6"
        align="right"
      >
        <v-btn
          icon
        >
          <v-icon class="primary--text">
            mdi-email
          </v-icon>
        </v-btn>
        <v-btn
          color="success"
          rounded
        >
          {{ carpoolRequests }}&nbsp;{{ carpoolRequests > 1 ? $t('potentialCarpooler.plural') : $t('potentialCarpooler.singular') }}
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/proposal/ProposalFooter.js";
import TranslationsClient from "@clientTranslations/components/user/profile/proposal/ProposalFooter.js";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    seats: {
      type: Number,
      default: null
    },
    price: {
      type: String,
      default: null
    },
    carpoolRequests: {
      type: Number,
      default: 0
    }
  }
}
</script>

<style scoped>

</style>
<template>
  <v-container fluid>
    <v-row>
      <v-col
        cols="3"
        class="primary--text"
      >
        <span v-if="seats && seats > 0">{{ seats }}&nbsp;{{ seats > 1 ? $t('seat.plural') : $t('seat.singular') }}</span>
      </v-col>
      <v-col
        cols="3"
        class="primary--text"
      >
        <span v-if="price && price > '0'">{{ price }} €</span>
      </v-col>
      <v-col
        cols="6"
        align="right"
      >
        <v-btn
          color="secondary"
          rounded
          :disabled="nbMatchings <= 0"
          :href="$t('urlResult',{id:id})"
        >
          {{ nbMatchings }}&nbsp;{{ nbMatchings > 1 ? $t('potentialCarpooler.plural') : $t('potentialCarpooler.singular') }}
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ad/AdFooter/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    id: {
      type: Number,
      default: null
    },
    seats: {
      type: Number,
      default: null
    },
    price: {
      type: String,
      default: null
    },
    idMessage: {
      type: Number,
      default: -1
    },
    nbMatchings:{
      type: Number,
      default: 0
    }
  }
}
</script>

<style scoped>

</style>
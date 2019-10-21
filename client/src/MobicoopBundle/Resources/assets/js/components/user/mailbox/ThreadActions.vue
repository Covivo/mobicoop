<template>
  <v-content>
    <v-card
      class="pa-2 text-center"
    >
      <v-card-text class="font-weight-bold headline">
        {{ recipientName }}
      </v-card-text>

      <v-card
        v-if="idAskHistory"
        class="mb-3"
      />
      <v-card v-else>
        <v-card-text>{{ $t("notLinkedToACarpool") }}</v-card-text>
      </v-card>
    </v-card>
  </v-content>
</template>
<script>
import Translations from "@translations/components/user/mailbox/ThreadActions.json";

export default {
  i18n: {
    messages: Translations,
  },
  props: {
    idAskHistory: {
      type: Number,
      default: null
    },
    recipientName: {
      type: String,
      default: null
    }
  }
}
</script>
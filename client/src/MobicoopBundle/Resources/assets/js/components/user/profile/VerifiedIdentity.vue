<template>
  <v-tooltip top>
    <template v-slot:activator="{ on, attrs }">
      <v-icon
        :color="verifiedIdentity ? 'primary' : 'amber accent-2'"
        v-bind="attrs"
        v-on="on"
      >
        mdi-badge-account-horizontal-outline
      </v-icon>
    </template>
    <span v-if="verifiedIdentity">{{ $t('identity.verified') }}</span>
    <span v-else>{{ $t('identity.unverified') }}</span>
  </v-tooltip>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/PublicProfile/";

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
    verifiedIdentity: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style>

</style>

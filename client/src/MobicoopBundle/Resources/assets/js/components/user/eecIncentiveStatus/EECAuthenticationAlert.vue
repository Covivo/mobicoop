<template>
  <v-alert
    v-if="!eecSubscriptions.authenticationValidity"
    class="mt-5"
    outlined
    type="warning"
    prominent
  >
    <p v-html="$t('authenticationAlert.text')" />
    <p class="text-center mt-10">
      <SsoLogins
        :specific-service="$t('service')"
        :specific-path="$t('specificPath')"
        :default-buttons-active="true"
      />
    </p>
  </v-alert>
</template>
<script>
import { merge } from "lodash";

import SsoLogins from '@components/user/SsoLogins';

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/EECIncentiveStatus/";

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
  components: {
    SsoLogins
  },
  props: {
    eecSubscriptions: {
      type: Object,
      default: () => ({})
    },
  }
}
</script>

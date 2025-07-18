<template>
  <div>
    <v-alert type="info" outlined>
      <p>
        <strong>{{ $t("title") }}</strong>
      </p>
      <p v-if="user.missingDataToPayElectronically.length > 0">
        {{ $t("text") }} :<br />{{ missingData }}
      </p>
    </v-alert>
  </div>
</template>

<script>
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/user/profile/MissingDataToPayElectronically/";
export default {
  i18n: {
    messages: {
      en: messages_en,
      nl: messages_nl,
      fr: messages_fr,
      eu: messages_eu
    }
  },
  props: {
    user: {
      type: Object,
      default: null
    }
  },
  data() {
    return {};
  },
  computed: {
    missingData() {
      return this.user.missingDataToPayElectronically
        .map(data => this.$t(data))
        .join(", ");
    }
  }
};
</script>

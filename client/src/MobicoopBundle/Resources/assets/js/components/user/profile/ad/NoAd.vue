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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ad/NoAd/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu': messages_eu
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

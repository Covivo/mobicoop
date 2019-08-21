<template>
  <div>
    <v-toolbar
      flat
      color="primary"
    >
      <v-toolbar-title>
        <a href="/">
          <v-img
            class="logo"
            src="/images/logo.png"
            alt="Mobicoop"
          />
        </a>
      </v-toolbar-title>
      <v-spacer />
      <div v-if="user">
        <v-btn
          text
          :href="$t('buttons.messages.route')"
        >
          {{ $t('buttons.messages.label') }}
        </v-btn>
        <v-btn
          text
          :href="$t('buttons.myProfile.route')"
        >
          {{ $t('buttons.myProfile.label') }}
        </v-btn>
        <v-btn
          text
          :href="$t('buttons.logOut.route')"
        >
          {{ $t('buttons.logOut.label') }}
        </v-btn>
      </div>
      <div v-else>
        <v-btn
          text
          :href="$t('buttons.signup.route')"
        >
          {{ $t('buttons.signup.label') }}
        </v-btn>
        <v-btn
          text
          :href="$t('buttons.logIn.route')"
        >
          {{ $t('buttons.logIn.label') }}
        </v-btn>
      </div>
      <v-btn
        rounded
        :href="$t('buttons.shareAnAd.route')"
      >
        {{ $t('buttons.shareAnAd.label') }}
      </v-btn>
    </v-toolbar>
  </div>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/base/MHeader.json";
import TranslationsClient from "@clientTranslations/components/base/MHeader.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    user: {
      type: Object,
      default: null
    }
  }
};
</script>
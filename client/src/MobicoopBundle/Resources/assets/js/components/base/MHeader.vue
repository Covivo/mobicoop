<template>
  <div>
    <v-toolbar
      flat
      color="primary"
    >
      <v-toolbar-title>
        <a href="/">
          <img
            class="logo"
            src="/images/logo.png"
            alt="Mobicoop"
          >
        </a>
      </v-toolbar-title>
      <v-spacer />
      <!--<accessibility />-->
      <div v-if="user">
        <v-btn
          text
          rounded
          :href="$t('buttons.messages.route')"
        >
          {{ $t('buttons.messages.label') }}
        </v-btn>
        <MHeaderCommunities />
        <MHeaderProfile
          :avatar="user.avatar"
          :short-family-name="(user.shortFamilyName) ? user.shortFamilyName : '-'"
        />
      </div>
      <div v-else>
        <v-btn
          rounded
          text
          :href="$t('buttons.signup.route')"
        >
          {{ $t('buttons.signup.label') }}
        </v-btn>
        <v-btn
          rounded
          text
          :href="$t('buttons.logIn.route')"
        >
          {{ $t('buttons.logIn.label') }}
        </v-btn>
      </div>
      <v-btn
        rounded
        :disabled="!user"
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
//import Accessibility from "@components/utilities/Accessibility";
import MHeaderProfile from "@components/base/MHeaderProfile.vue";
import MHeaderCommunities from "@components/base/MHeaderCommunities.vue";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    //Accessibility,
    MHeaderProfile,
    MHeaderCommunities
  },
  props: {
    user: {
      type: Object,
      default: null
    }
  }
};
</script>
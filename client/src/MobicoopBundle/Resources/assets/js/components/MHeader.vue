<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title>
        <a href="/"><v-img
          class="logo"
          src="/images/logo.png"
          alt="Mobicoop"
        /></a>
      </v-toolbar-title>
      <v-spacer />
      <div
        v-if="user"
      >
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
import Translations from "../../../translations/components/MHeader.json";

export default {
  i18n: {
    messages: Translations
  },
  props: {
    user: {
      type: Object,
      default: null
    }
  }
}
</script>
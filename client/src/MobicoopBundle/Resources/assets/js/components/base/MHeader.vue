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
        <MHeaderCommunities :user-id="user.id" />
        <MHeaderProfile
          :avatar="user.avatars[0]"
          :short-family-name="(user.shortFamilyName) ? user.givenName+' '+user.shortFamilyName : '-'"
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
    },
    urlMobile: {
      type: String,
      default: null
    },
  },
  mounted() {
    if (this.urlMobile && (screen.width <= 960 || navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Windows Phone/i))) 
    {
      window.location.href = this.urlMobile;
    } 
  }
};
</script>
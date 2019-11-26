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
      <v-toolbar-items
        v-if="user"
        class="hidden-sm-and-down"
      >
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
      </v-toolbar-items>
      <v-menu
        v-if="user"
        class="hidden-md-and-up"
      >
        <v-toolbar-side-icon slot="activator" />
        <v-list>
          <v-list-tile
            v-for="item in menu"
            :key="item.icon"
          >
            <v-list-tile-content>
              <v-list-tile-title>
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
              </v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list>
      </v-menu>
      <v-toolbar-items
        v-else
        class="hidden-sm-and-down"
      >
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
      </v-toolbar-items>
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
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
        class="hidden-md-and-down"
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
      <v-toolbar-items
        v-else
        class="hidden-md-and-down"
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
        v-if="user"
        rounded
        :href="$t('buttons.shareAnAd.route')"
        class="hidden-md-and-down"
      >
        {{ $t('buttons.shareAnAd.label') }}
      </v-btn>
      <div @click="snackbar = true">
        <v-btn
          v-if="!user"
          rounded
          disabled
          class="hidden-md-and-down"
        >
          {{ $t('buttons.shareAnAd.label') }}
        </v-btn>
      </div>
      <v-snackbar
        v-if="!user"
        v-model="snackbar"
        color="info"
      >
        {{ $t('snackbar.needConnection') }}
        <v-btn
          color="info"
          icon
          elevation="0"
          @click="snackbar = false"
        >
          <v-icon
            color="primary"
          >
            mdi-close
          </v-icon>
        </v-btn>
      </v-snackbar>


      <!--Display menu when user is connected-->
      <v-menu
        v-if="user"
        bottom
        left
        z-index="9"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            icon
            class="hidden-lg-and-up"
            v-on="on"
          >
            <v-icon>mdi-menu</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item>
            <v-list-item-title>
              <MHeaderProfile
                :avatar="user.avatars[0]"
                :short-family-name="(user.shortFamilyName) ? user.givenName+' '+user.shortFamilyName : '-'"
              />
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <MHeaderCommunities :user-id="user.id" />
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :disabled="!user"
                :href="$t('buttons.shareAnAd.route')"
              >
                {{ $t('buttons.shareAnAd.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :href="$t('buttons.messages.route')"
              >
                {{ $t('buttons.messages.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>


      <!--Display menu when there is no connected user-->
      <v-menu
        v-else
        bottom
        left
        z-index="9"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            icon
            class="hidden-lg-and-up"
            v-on="on"
          >
            <v-icon>mdi-menu</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :href="$t('buttons.messages.route')"
              >
                {{ $t('buttons.messages.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                rounded
                text
                :href="$t('buttons.signup.route')"
              >
                {{ $t('buttons.signup.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                rounded
                text
                :href="$t('buttons.logIn.route')"
              >
                {{ $t('buttons.logIn.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :disabled="!user"
                :href="$t('buttons.shareAnAd.route')"
              >
                {{ $t('buttons.shareAnAd.label') }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
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
  data () {
    return {
      snackbar: false
    }
  },
  mounted() {
    if (this.urlMobile && (screen.width <= 960 || navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Windows Phone/i))) 
    {
      window.location.href = this.urlMobile;
    } 
  }
};
</script>
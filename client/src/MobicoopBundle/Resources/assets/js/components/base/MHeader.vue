<template>
  <div>
    <v-toolbar
      flat
      color="primary"
      height="80px"
    >
      <v-toolbar-title align="midle">
        <a
          href="/"
          class="d-flex align-center"
        >
          <img
            class="logo"
            :src="imageLink + 'MOBICOOP_LOGO-V1 Blanc.svg'"
            alt="Mobicoop"
            height="50"
            width="280"
            contain
            eager
          >
        </a>
      </v-toolbar-title>

      <v-spacer />

      <!--<accessibility />-->
      <div
        v-if="user"
        class="hidden-md-and-down"
      >
        <v-btn
          text
          rounded
          class="white--text title text-none"
          :href="$t('buttons.messages.route')"
        >
          {{ $t('buttons.messages.label') }}
        </v-btn>
        <MHeaderCommunities
          :user-id="user.id" 
          :text-color-class="textColorClass"
        />
        <MHeaderProfile
          :avatar="user.avatars[0]"
          :short-family-name="(user.shortFamilyName) ? user.givenName+' '+user.shortFamilyName : '-'"
          :show-reviews="showReviews"
          :text-color-class="textColorClass"
        />
      </div>
      <v-toolbar-items
        v-else
        class="hidden-md-and-down"
      >
        <v-btn
          class="white--text"
          rounded
          text
          :href="$t('buttons.signup.route')"
        >
          {{ $t('buttons.signup.label') }}
        </v-btn>
        <v-btn
          class="white--text"
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
        color="secondary"
        :href="$t('buttons.shareAnAd.route')"
        class="hidden-md-and-down white--text mr-4"
      >
        {{ $t('buttons.shareAnAd.label') }}
      </v-btn>
      <v-btn
        rounded
        color="secondary"
        :href="$t('buttons.solidary.route')"
        class="hidden-md-and-down white--text mr-4"
      >
        {{ $t('buttons.solidary.label') }}
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
      <v-toolbar-items
        class="hidden-md-and-down"
      >
        <MHeaderLanguage
          :languages="languages"
          :language="dlocale"
          @languageSelected="updateLanguage"
        />
      </v-toolbar-items>
      <v-snackbar
        v-if="!user"
        v-model="snackbar"
        top
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
              <MHeaderLanguage
                :languages="languages"
                :language="dlocale"
                @languageSelected="updateLanguage"
              />
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <MHeaderProfile
                :avatar="user.avatars[0]"
                :short-family-name="(user.shortFamilyName) ? user.givenName+' '+user.shortFamilyName : '-'"
                :show-reviews="showReviews"
                :text-color-class="textColorClass"
              />
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <MHeaderCommunities
                :user-id="user.id"
              />
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
import { merge, has } from "lodash";
import {messages_en, messages_fr} from "@translations/components/base/MHeader/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/base/MHeader/";
//import Accessibility from "@components/utilities/Accessibility";
import MHeaderProfile from "@components/base/MHeaderProfile.vue";
import MHeaderCommunities from "@components/base/MHeaderCommunities.vue";
import MHeaderLanguage from "@components/base/MHeaderLanguage.vue";


let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  components: {
    //Accessibility,
    MHeaderProfile,
    MHeaderCommunities,
    MHeaderLanguage
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
    locale: {
      type: String,
      default: "fr"
    },
    showReviews: {
      type: Boolean,
      default: false
    },
    languages: {
      type: Object,
      default: () => {}
    },
    token: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      snackbar: false,
      width: 0,
      defaultLocale: 'fr',
      dlocale: this.locale,
      imageLink: "/images/pages/home/",
      textColorClass: "white--text title text-none"
    }
  },
  mounted() {
    if (has(this.languages, this.locale)) {
      this.dlocale = this.locale;
    } else {
      this.dlocale = this.defaultLocale;
    }
  },
  created() {
    this.$root.token = this.token;
    this.$root.$i18n.locale = this.dlocale
  },
  methods:{
    updateLanguage(language) {
      this.$root.$i18n.locale = language
    },
  }
};
</script>
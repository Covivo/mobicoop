<template>
  <div>
    <v-row
      id="pad"
      role="navigation"
    >
      <v-col
        cols="4"
        md="2"
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.carpool.link')"
          :alt="$t('pad.carpool.title')"
          target="_blank"
        >{{ $t("pad.carpool.title") }}</a>
      </v-col>
      <v-col
        cols="4"
        md="2"
        class="section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.mobility.link')"
          :alt="$t('pad.mobility.title')"
          target="_blank"
        >{{ $t("pad.mobility.title") }}</a><br>
        <a
          :href="$t('pad.mobility.link')"
          :alt="$t('pad.mobility.title')"
          target="_blank"
          class="font-italic text-lowercase"
        >{{ $t("pad.mobility.subtitle") }}</a>
      </v-col>
      <v-col
        cols="4"
        md="2"
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.events.link')"
          :alt="$t('pad.events.title')"
          target="_blank"
        >{{ $t("pad.events.title") }}</a>
      </v-col>
      <v-col
        cols="4"
        md="2"
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.subscribe.link')"
          :alt="$t('pad.subscribe.title')"
          target="_blank"
        >{{ $t("pad.subscribe.title") }}</a>
      </v-col>
      <v-col
        cols="4"
        md="2"
        class="d-flex section justify-center align-center text-center text-uppercase text-body-2 pa-1"
      >
        <a
          :href="$t('pad.blog.link')"
          :alt="$t('pad.blog.title')"
          target="_blank"
        >{{ $t("pad.blog.title") }}</a>
      </v-col>
      <v-col
        cols="4"
        md="2"
        class="d-lg-flex social justify-center align-center text-center text-center text-uppercase text-body-2 pa-1 justify-spacebetween"
      >
        <a
          :href="$t('pad.social.facebook.link')"
          :aria-label="$t('pad.social.facebook.title')"
          target="_blank"
        >
          <v-icon class="white--text mx-2">
            mdi-facebook
          </v-icon></a>
        <a
          :href="$t('pad.social.twitter.link')"
          :aria-label="$t('pad.social.twitter.title')"
          target="_blank"
        >
          <v-icon class="white--text mx-2">
            mdi-twitter
          </v-icon>
        </a>
        <a
          :href="$t('pad.social.linkedin.link')"
          :aria-label="$t('pad.social.linkedin.title')"
          target="_blank"
        >
          <v-icon class="white--text mx-2">
            mdi-linkedin
          </v-icon>
        </a>
      </v-col>
    </v-row>
    <v-toolbar
      flat
      color="primary"
      height="80px"
      role="banner"
    >
      <v-toolbar-title>
        <a
          href="/"
          class="d-flex"
        >
          <img
            class="logo"
            :src="imageLink + 'MOBICOOP_LOGO-V1 Blanc.svg'"
            :alt="
              $te('logo_alt')
                ? $t('logo_alt', { platform: appName.toLowerCase() })
                : $t('logo')
            "
            height="50"
            width="210"
          >
          <!-- <v-img
            class="logo"
            :src="imageLink + 'MOBICOOP_LOGO-V1 Blanc.svg'"
            :alt="$te('logo_alt') ? $t('logo_alt', { platform: 'mobicoop'}) : $t('logo')"
            height="50"
            width="210"
            contain
            eager
          /> -->
        </a>
      </v-toolbar-title>

      <v-spacer />

      <!--<accessibility />-->
      <div
        v-if="user"
        class="hidden-md-and-down"
      >
        <MMessageBtn :unread-message-number="unreadMessageNumber" />
        <MHeaderCommunities
          :user-id="user.id"
          :text-color-class="textColorClass"
        />
        <MHeaderProfile
          :avatar="user.avatars[0]"
          :short-family-name="
            user.shortFamilyName
              ? user.givenName + ' ' + user.shortFamilyName
              : '-'
          "
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
          {{ $t("buttons.signup.label") }}
        </v-btn>
        <v-btn
          class="white--text"
          rounded
          text
          :href="$t('buttons.logIn.route')"
        >
          {{ $t("buttons.logIn.label") }}
        </v-btn>
      </v-toolbar-items>
      <v-btn
        v-if="user || publishButtonAlwaysActive == true"
        rounded
        color="secondary"
        :href="$t('buttons.shareAnAd.route')"
        class="hidden-md-and-down white--text mr-4"
        small
        :aria-label="$t('buttons.shareAnAd.label')"
      >
        {{ $t("buttons.shareAnAd.label") }}
      </v-btn>

      <MHeaderLanguage
        :languages="languages"
        :language="dlocale"
        class="hidden-md-and-down"
        @languageSelected="updateLanguage"
      />
      <v-snackbar
        v-if="!user"
        v-model="snackbar"
        top
        color="info"
      >
        {{ $t("snackbar.needConnection") }}
        <v-btn
          color="info"
          icon
          elevation="0"
          @click="snackbar = false"
        >
          <v-icon color="primary">
            mdi-close
          </v-icon>
        </v-btn>
      </v-snackbar>

      <!--Display menu when user is connected-->
      <v-menu
        v-if="user"
        bottom
        left
        role="menu"
        z-index="9"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            icon
            class="hidden-lg-and-up"
            aria-label="menu"
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
                :short-family-name="
                  user.shortFamilyName
                    ? user.givenName + ' ' + user.shortFamilyName
                    : '-'
                "
                :show-reviews="showReviews"
                :text-color-class="textColorClass"
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
                :aria-label="$t('buttons.shareAnAd.label')"
              >
                {{ $t("buttons.shareAnAd.label") }}
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
                {{ $t("buttons.messages.label") }}
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
            aria-label="menu"
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
                {{ $t("buttons.messages.label") }}
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
                {{ $t("buttons.signup.label") }}
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
                {{ $t("buttons.logIn.label") }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>
              <v-btn
                text
                rounded
                :disabled="!user && publishButtonAlwaysActive == false"
                :href="$t('buttons.shareAnAd.route')"
                :aria-label="$t('buttons.shareAnAd.label')"
              >
                {{ $t("buttons.shareAnAd.label") }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-toolbar>
    <GamificationNotifications
      :user-gamification-notifications="
        user && user.gamificationNotifications
          ? user.gamificationNotifications
          : null
      "
    />
    <GratuityNotifications
      :user-gratuity-notifications="
        user && user.gratuityNotifications ? user.gratuityNotifications : null
      "
    />
    <!-- legal guardian dialog -->
    <v-dialog
      v-if="user"
      v-model="activeLegalGuardianDialog"
      persistent
      max-width="900"
    >
      <v-card>
        <v-card-title class="text-h5 justify-center">
          {{ $t("dialog.parentalConsent.title") }}
        </v-card-title>
        <v-card-text
          v-html="
            $t('dialog.parentalConsent.content', {
              legalGuardianEmail: user.legalGuardianEmail
            })
          "
        />
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="secondary"
            primary
            rounded
            :href="this.$t('buttons.logOut.route')"
          >
            {{ $t("dialog.parentalConsent.buttonLabel") }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { merge, has } from "lodash";
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/base/MHeader/";
import {
  messages_client_en,
  messages_client_fr,
  messages_client_eu,
  messages_client_nl
} from "@clientTranslations/components/base/MHeader/";
//import Accessibility from "@components/utilities/Accessibility";
import MHeaderProfile from "@components/base/MHeaderProfile.vue";
import MHeaderCommunities from "@components/base/MHeaderCommunities.vue";
import MHeaderLanguage from "@components/base/MHeaderLanguage.vue";
import MMessageBtn from "@components/base/MMessageBtn.vue";
import GamificationNotifications from "@components/utilities/gamification/GamificationNotifications";
import GratuityNotifications from "@components/utilities/gratuity/GratuityNotifications";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      en: MessagesMergedEn,
      nl: MessagesMergedNl,
      fr: MessagesMergedFr,
      eu: MessagesMergedEu
    }
  },
  components: {
    //Accessibility,
    MHeaderProfile,
    MHeaderCommunities,
    MHeaderLanguage,
    MMessageBtn,
    GamificationNotifications,
    GratuityNotifications
  },
  props: {
    appName: {
      type: String,
      default: null
    },
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
      default: ""
    },
    publishButtonAlwaysActive: {
      type: Boolean,
      default: false
    },
    gamificationActive: {
      type: Boolean,
      default: false
    },
    gratuityActive: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      snackbar: false,
      width: 0,
      defaultLocale: "fr",
      dlocale: this.locale,
      imageLink: "/images/pages/home/",
      textColorClass: "white--text title text-none",
      activeLegalGuardianDialog: false
    };
  },
  computed: {
    unreadMessageNumber() {
      return (
        this.$store.getters["m/unreadCarpoolMessageNumber"] +
        this.$store.getters["m/unreadDirectMessageNumber"] +
        this.$store.getters["m/unreadSolidaryMessageNumber"]
      );
    }
  },
  mounted() {
    if (has(this.languages, this.locale)) {
      this.dlocale = this.locale;
    } else {
      this.dlocale = this.defaultLocale;
    }
    localStorage.setItem("X-LOCALE", this.dlocale);
    this.$store.commit("g/setActive", this.gamificationActive);
    this.$store.commit("grt/setActive", this.gratuityActive);
    this.$store.commit(
      "m/setUnreadCarpoolMessageNumber",
      this.user?.unreadCarpoolMessageNumber
    );
    this.$store.commit(
      "m/setUnreadDirectMessageNumber",
      this.user?.unreadDirectMessageNumber
    );
    this.$store.commit(
      "m/setUnreadSolidaryMessageNumber",
      this.user?.unreadSolidaryMessageNumber
    );
  },
  created() {
    this.$store.commit(
      "a/setToken",
      this.user?.token ? this.user.token : this.token
    );
    if (this.user) {
      localStorage.setItem("X-LOCALE", this.dlocale);
    }
    if (this.user?.needParentalConsent) {
      this.activeLegalGuardianDialog = true;
    }
    this.$root.$i18n.locale = this.dlocale;
  },
  methods: {
    updateLanguage(language) {
      this.$root.$i18n.locale = language;
    }
  }
};
</script>
<style lang="scss" scoped>
#pad {
  background-color: #007b85;
  margin: 0px;
  color: white;
  font-family: Poppins, sans-serif !important;
  a {
    text-decoration: none;
    color: white;
    font-family: Poppins, sans-serif !important;
  }
  .section {
    border-right: 1px solid white;
    margin: 0px;
    vertical-align: middle;
  }
  .social {
    margin: 0px;
    padding: 0px;
    vertical-align: middle;
  }
}
</style>

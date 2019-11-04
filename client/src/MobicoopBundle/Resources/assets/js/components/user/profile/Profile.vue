<template>
  <v-container fluid>
    <v-row 
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <h1>{{ $t('page.title') }}</h1>
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col cols="8">
        <v-tabs
          slider-color="secondary"
          color="secondary"
          vertical
        >
          <v-tab>{{ $t("tabs.myProfile") }}</v-tab>
          <v-tab-item>
            <UpdateProfile
              :user="user"
              :geo-search-url="geoSearchUrl"
              :age-min="ageMin"
              :age-max="ageMax"
              :avatar-size="avatarSize"
              :url-alt-avatar="urlAltAvatar"
              :avatar-version="avatarVersion"
            />
          </v-tab-item>
          <v-tab>{{ $t("tabs.password") }}</v-tab>
          <v-tab-item>
            <ChangePassword />
          </v-tab-item>
          <v-tab>{{ $t("tabs.myProposals") }}</v-tab>
          <v-tab-item>
            <MyProposals :proposals="proposals" />
          </v-tab-item>
          <v-tab>{{ $t("tabs.alerts") }}</v-tab>
          <v-tab-item>
            <Alerts :alerts="alerts" />
          </v-tab-item>
          <v-tab>{{ $t("tabs.carpoolSettings") }}</v-tab>
          <v-tab-item>
            <CarpoolSettings :user="user" />
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import UpdateProfile from "@components/user/profile/UpdateProfile";
import ChangePassword from "@components/user/profile/ChangePassword";
import MyProposals from "@components/user/profile/MyProposals";
import Alerts from "@components/user/profile/Alerts";
import CarpoolSettings from "@components/user/profile/CarpoolSettings";

import { merge } from "lodash";
import Translations from "@translations/components/user/profile/Profile.json";
import TranslationsClient from "@clientTranslations/components/user/profile/Profile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    UpdateProfile,
    ChangePassword,
    MyProposals,
    Alerts,
    CarpoolSettings
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    ageMin: {
      type: String,
      default: null
    },
    ageMax: {
      type: String,
      default: null
    },
    avatarSize: {
      type: String,
      default: null
    },
    avatarVersion: {
      type: String,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    alerts: {
      type: Array,
      default: null
    },
    proposals: {
      type: Array,
      default: () => []
    }
  }
}
</script>
<template>
  <v-container fluid>
    <v-row
      justify="center"
    >
      <v-col cols="11">
        <v-tabs
          slider-color="secondary"
          color="secondary"
          vertical
          :value="tabItemIndex"
        >
          <v-tab class="text-left justify-start ml-2 mr-5 title">
            {{ $t("tabs.myProposals") }}
          </v-tab>
          <v-tab-item>
            <MyProposals :proposals="proposals" />
          </v-tab-item>
          <v-tab class="text-left justify-start ml-2 mr-5 title">
            {{ $t("tabs.carpoolsAccepted") }}
          </v-tab>
          <v-tab-item>
            <v-alert
              type="info"
              class="text-center"
            >
              En cours de développement
            </v-alert>
          </v-tab-item>
          <v-tab class="text-left justify-start ml-2 mr-5 title">
            {{ $t("tabs.myProfile") }}
          </v-tab>
          <v-tab-item>
            <v-tabs grow>
              <v-tab class="subtitle-1">
                {{ $t("tabs.myAccount") }}
              </v-tab>
              <v-tab-item>
                <UpdateProfile
                  :user="user"
                  :geo-search-url="geoSearchUrl"
                  :age-min="ageMin"
                  :age-max="ageMax"
                  :avatar-size="avatarSize"
                  :url-alt-avatar="urlAltAvatar"
                  :avatar-version="avatarVersion"
                  :platform="platform"
                />
              </v-tab-item>
              <v-tab class="subtitle-1">
                {{ $t("tabs.alerts") }}
              </v-tab>
              <v-tab-item>
                <Alerts :alerts="alerts" />
              </v-tab-item>
              <v-tab class="subtitle-1">
                {{ $t("tabs.carpoolSettings") }}
              </v-tab>
              <v-tab-item>
                <CarpoolSettings :user="user" />
              </v-tab-item>
            </v-tabs>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import UpdateProfile from "@components/user/profile/UpdateProfile";
import MyProposals from "@components/user/profile/proposal/MyProposals";
import Alerts from "@components/user/profile/Alerts";
import CarpoolSettings from "@components/user/profile/CarpoolSettings";

import {merge} from "lodash";
import Translations from "@translations/components/user/profile/Profile.json";
import TranslationsClient from "@clientTranslations/components/user/profile/Profile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    UpdateProfile,
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
    platform: {
      type: String,
      default: ""
    },
    proposals: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      tabItemIndex: null
    }
  },
  mounted () {
    let urlString = window.location.href;
    let url = new URL(urlString);
    this.tabItemIndex = url.searchParams.get("t") ? parseInt(url.searchParams.get("t")) : 0;
  }
}
</script>
<style lang="scss" scoped>
.v-tab{
  text-transform: initial !important;
}
</style>
<template>
  <v-container fluid>
    <v-row
      justify="center"
    >
      <v-col cols="8">
        <v-tabs
          v-model="modelTabs"
          slider-color="primary"
          color="primary"
          vertical
        >
          <v-tab
            class="text-left justify-start ml-2 mr-5 title"
            href="#myAds"
          >
            {{ $t("tabs.myAds") }}
          </v-tab>
          <v-tab-item value="myAds">
            <Ads :ads="ads" />
          </v-tab-item>
          <v-tab
            class="text-left justify-start ml-2 mr-5 title"
            href="#carpoolsAccepted"
          >
            {{ $t("tabs.carpoolsAccepted") }}
          </v-tab>
          <v-tab-item value="carpoolsAccepted">
            <carpools :accepted-carpools="acceptedCarpools" />
          </v-tab-item>
          <v-tab
            class="text-left justify-start ml-2 mr-5 title"
            href="#myProfile"
          >
            {{ $t("tabs.myProfile") }}
          </v-tab>
          <v-tab-item
            value="myProfile"
            primary
            lighten-5
          >
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
import Ads from "@components/user/profile/ad/Ads";
import Carpools from "@components/user/profile/carpool/Carpools";
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
    Ads,
    Alerts,
    CarpoolSettings,
    Carpools
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
      type: Object,
      default: null
    },
    platform: {
      type: String,
      default: ""
    },
    ads: {
      type: Object,
      default: () => {}
    },
    acceptedCarpools: {
      type: Object,
      default: () => {}
    },
    tabDefault: {
      type: String,
      default: null
    }
  },
  data(){
    return{
      modelTabs:(this.tabDefault !== "") ? this.tabDefault : "myAds"
    }
  }
}
</script>
<style lang="scss" scoped>
.v-tab{
  text-transform: initial !important;
}
</style>
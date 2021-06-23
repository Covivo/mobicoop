<template>
  <v-container fluid>
    <v-row
      justify="center"
    >
      <v-col
        cols="8"
        md="12"
        lg="10"
        xl="8"
      >
        <!-- VERTICAL TABS -->
        <v-tabs
          v-model="modelTabs"
          slider-color="primary"
          color="primary"
          vertical
        >
          <!-- ADS -->
          <v-tab
            class="text-left justify-start ml-2 mr-5 text-h6"
            href="#myAds"
          >
            {{ $t("tabs.myAds") }}
          </v-tab>
          <v-tab-item value="myAds">
            <Ads :ads="publishedAds" />
          </v-tab-item>

          <!-- ACCEPTED CARPOOLS -->
          <v-tab
            class="text-left justify-start ml-2 mr-5 text-h6"
            href="#carpoolsAccepted"
          >
            {{ $t("tabs.carpoolsAccepted") }}
          </v-tab>
          <v-tab-item value="carpoolsAccepted">
            <Carpools
              :carpools="acceptedAds"
              :user="user"
              :payment-electronic-active="paymentElectronicActive"
            />
          </v-tab-item>
          
          <!-- PROFILE -->
          <v-tab
            class="text-left justify-start ml-2 mr-5 text-h6"
            href="#myProfile"
          >
            {{ $t("tabs.myProfile") }}
          </v-tab>
          <v-tab-item
            value="myProfile"
            primary
            lighten-5
          >
            <!-- HORIZONTAL SUB TABS -->
            <v-tabs grow>
              <v-tab class="text-subtitle-1">
                {{ $t("tabs.myAccount") }}
              </v-tab>
              <!-- ACCOUNT -->
              <v-tab-item>
                <div class="text-right">
                  <v-btn
                    class="mt-4"
                    color="primary"
                    rounded
                    @click="dialog = true"
                  >
                    {{ $t('publicProfile.see') }}
                  </v-btn>
                </div>
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

              <!-- ALERTS -->
              <v-tab class="text-subtitle-1">
                {{ $t("tabs.alerts") }}
              </v-tab>
              <v-tab-item>
                <Alerts :alerts="alerts" />
              </v-tab-item>

              <!-- SETTINGS -->
              <v-tab class="text-subtitle-1">
                {{ $t("tabs.carpoolSettings") }}
              </v-tab>
              <v-tab-item>
                <CarpoolSettings :user="user" />
              </v-tab-item>

              <!-- BANK COORDINATES -->
              <v-tab
                v-if="paymentElectronicActive"
                class="text-subtitle-1"
              >
                {{ $t("tabs.bankCoordinates") }}
              </v-tab>
              <v-tab-item v-if="paymentElectronicActive">
                <BankAccount
                  :user="user"
                  :geo-search-url="geoSearchUrl"
                  :validation-docs-authorized-extensions="validationDocsAuthorizedExtensions"
                />
              </v-tab-item>              
            </v-tabs>
          </v-tab-item>

          <!-- REVIEW DASHBOARD -->
          <v-tab
            v-if="showReviews"
            class="text-left justify-start ml-2 mr-5 text-h6"
            href="#reviews"
          >
            {{ $t("tabs.reviews") }}
          </v-tab>
          <v-tab-item
            v-if="showReviews"
            value="reviews"
          >
            <ReviewDashboard />
          </v-tab-item>

          <!-- PROFILE SUMMARY -->
          <div>
            <ProfileSummary
              :user-id="user.id"
              :show-link-profile="false"
            />
          </div>
        </v-tabs>
      </v-col>
    </v-row>

    <!-- PUBLIC PROFILE DIALOG -->
    <v-dialog
      v-model="dialog"
      width="100%"
    >
      <v-card>
        <v-card-title class="headline grey lighten-2">
          {{ $t('publicProfile.title') }}
        </v-card-title>

        <v-card-text>
          <PublicProfile
            :user="user"
            :show-report-button="false"
            :age-display="ageDisplay"
          />
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-spacer />
          <v-btn
            color="primary"
            text
            @click="dialog = false"
          >
            {{ $t('publicProfile.close') }}
          </v-btn>
        </v-card-actions>
      </v-card>      
    </v-dialog>    
  </v-container>
</template>

<script>
import maxios from "@utils/maxios";
import UpdateProfile from "@components/user/profile/UpdateProfile";
import Ads from "@components/user/profile/ad/Ads";
import Carpools from "@components/user/profile/carpool/Carpools";
import Alerts from "@components/user/profile/Alerts";
import CarpoolSettings from "@components/user/profile/CarpoolSettings";
import BankAccount from "@components/user/profile/payment/BankAccount";
import ProfileSummary from "@components/user/profile/ProfileSummary";
import PublicProfile from "@components/user/profile/PublicProfile";
import ReviewDashboard from "@components/user/profile/review/ReviewDashboard";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/Profile/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    UpdateProfile,
    Ads,
    Alerts,
    CarpoolSettings,
    Carpools,
    BankAccount,
    ProfileSummary,
    PublicProfile,
    ReviewDashboard
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
    tabDefault: {
      type: String,
      default: null
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
    validationDocsAuthorizedExtensions: {
      type: String,
      default: null
    },
    showReviews: {
      type: Boolean,
      default: false
    },
    ageDisplay: {
      type: Boolean,
      default: true
    },
  },
  data(){
    return{
      modelTabs:(this.tabDefault !== "") ? this.tabDefault : "myAds",
      dialog:false,
      publishedAds: {},
      acceptedAds: {}
    }
  },
  mounted(){
    maxios.get(this.$t("getMyCarpools"))
      .then(res => {
        this.publishedAds = res.data.published;
        this.acceptedAds = res.data.accepted;
      })
      .catch(function (error) {
        
      });
  },
}
</script>
<style lang="scss" scoped>
.v-tab{
  text-transform: initial !important;
}
</style>
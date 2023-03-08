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
          v-model="modelTabsV"
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
            <Ads
              :ads="publishedAds"
              @ad-deleted="deleteAd"
            />
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
            <v-tabs
              v-model="modelTabsH"
              grow
            >
              <v-tab
                class="text-subtitle-1"
                :href="`#myAccount`"
              >
                {{ $t("tabs.myAccount") }}
              </v-tab>
              <!-- ACCOUNT -->
              <v-tab-item
                :value="'myAccount'"
              >
                <UpdateProfile
                  :user="user"
                  :geo-search-url="geoSearchUrl"
                  :geo-complete-results-order="geoCompleteResultsOrder"
                  :geo-complete-palette="geoCompletePalette"
                  :geo-complete-chip="geoCompleteChip"
                  :age-min="ageMin"
                  :age-max="ageMax"
                  :age-display="ageDisplay"
                  :image-min-px-size="imageMinPxSize"
                  :image-max-mb-size="imageMaxMbSize"
                  :url-alt-avatar="urlAltAvatar"
                  :platform="platform"
                  :gamification-active="gamificationActive"
                  :carpool-settings-display="carpoolSettingsDisplay"
                  :cee-display="ceeDisplay"
                  @changeTab="changeTab"
                />
              </v-tab-item>

              <!-- ALERTS -->
              <v-tab
                class="text-subtitle-1"
                href="#alerts"
              >
                {{ $t("tabs.alerts") }}
              </v-tab>
              <v-tab-item value="alerts">
                <Alerts :alerts="alerts" />
              </v-tab-item>

              <!-- SETTINGS -->
              <v-tab
                v-if="carpoolSettingsDisplay"
                class="text-subtitle-1"
              >
                {{ $t("tabs.carpoolSettings") }}
              </v-tab>
              <v-tab-item>
                <CarpoolSettings :user="user" />
              </v-tab-item>

              <!-- BANK COORDINATES -->
              <v-tab
                v-if="paymentElectronicActive"
                class="text-subtitle-1"
                href="#bankCoordinates"
              >
                {{ $t("tabs.bankCoordinates") }}
              </v-tab>
              <v-tab-item
                v-if="paymentElectronicActive"
                value="bankCoordinates"
              >
                <BankAccount
                  :user="user"
                  :geo-search-url="geoSearchUrl"
                  :geo-complete-results-order="geoCompleteResultsOrder"
                  :geo-complete-palette="geoCompletePalette"
                  :geo-complete-chip="geoCompleteChip"
                  :validation-docs-authorized-extensions="validationDocsAuthorizedExtensions"
                />
              </v-tab-item>
              <v-tab
                v-if="gamificationActive"
                class="text-subtitle-1"
                href="#myBadges"
              >
                {{ $t("tabs.myBadges") }}
              </v-tab>
              <!-- ACCOUNT -->
              <v-tab-item
                v-if="gamificationActive"
                value="myBadges"
              >
                <Badges />
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
            <ReviewDashboard
              :selected-tab="selectedTab"
            />
          </v-tab-item>

          <!-- PROFILE SUMMARY -->
          <div>
            <ProfileSummary
              :user-id="user.id"
              :show-link-profile="false"
              :verified-identity="user.verifiedIdentity"
              :show-verified-identity="user.verifiedIdentity !== null"
            />
          </div>
        </v-tabs>
      </v-col>
    </v-row>
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
import ReviewDashboard from "@components/user/profile/review/ReviewDashboard";
import Badges from "@components/user/profile/Badges";
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
    ReviewDashboard,
    Badges
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
    imageMinPxSize: {
      type: Number,
      default: null
    },
    imageMaxMbSize: {
      type: Number,
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
    selectedTab: {
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
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
    carpoolSettingsDisplay: {
      type: Boolean,
      default: true
    },
    ceeDisplay: {
      type: Boolean,
      default: true
    }
  },
  data(){
    return{
      modelTabsV:(this.tabDefault !== "") ? this.tabDefault : "myAds",
      modelTabsH:(this.selectedTab !== "") ? this.selectedTab : "myAccount",
      publishedAds: {},
      acceptedAds: {}
    }
  },
  computed:{
    gamificationActive(){
      return this.$store.getters['g/isActive'];
    }
  },
  mounted(){
    this.getAds();
  },
  methods: {
    getAds() {
      maxios.get(this.$t("getMyCarpools"))
        .then(res => {
          this.publishedAds = res.data.published;
          this.acceptedAds = res.data.accepted;
        })
        .catch(function (error) {

        });
    },
    deleteAd() {
      this.getAds()
    },
    changeTab(tab){
      this.modelTabsH = tab;
    }
  }
}
</script>
<style lang="scss" scoped>
.v-tab{
  text-transform: initial !important;
}
</style>

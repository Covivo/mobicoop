<template>
  <v-main color="secondary">
    <v-container
      text-center
      fluid
    >
      <v-row v-if="displayVerifiedMessage">
        <v-col>
          <v-snackbar
            v-model="snackbar"
            top
            multi-line
            color="info"
            vertical
            :timeout="10000"
          >
            <div>
              {{ $t('snackbar1') }}
              <a :href="$t('profileLink')">
                " {{ $t('myProfile') }} "
              </a>
              {{ $t('snackbar2') }}
            </div>
            <v-btn
              color="info"
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
        </v-col>
      </v-row>

      <v-row v-if="displayDeleteAccount">
        <v-col>
          <v-snackbar
            v-model="snackbar"
            top
            multi-line
            color="info"
            vertical
          >
            <div>
              {{ $t('snackbar-account-delete') }}
            </div>
            <v-btn
              color="info"
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
        </v-col>
      </v-row>
      <v-row v-if="displayUnsubscribeEmail">
        <v-col>
          <v-snackbar
            v-model="snackbar"
            top
            multi-line
            color="info"
            vertical
          >
            <div>
              {{ displayUnsubscribeEmail }}
            </div>
            <v-btn
              color="info"
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
        </v-col>
      </v-row>

      <div
        :style="'background-image:url(\''+$t('urlBackground')+'\');background-size:contain;background-position-y:-8em;'"
      >
        <!-- Title and subtitle -->
        <v-row
          align="center"
          justify="center"
          class="py-12 mt-n3"
        >
          <v-col
            class="text-center white--text mt-n2"
            :style="'font-size:1.25rem;'"
          >
            <h1 v-html="$t('title')" />
          </v-col>
        </v-row>
        <!-- end Title and subtitle -->

        <!-- search -->
        <v-row
          justify="center"
          class="mt-8"
        >
          <v-col
            lg="10"
            xl="8"
            class="mt-16"
          >
            <search
              :geo-search-url="geoSearchUrl"
              :user="user"
              :regular="regular"
              :punctual-date-optional="punctualDateOptional"
              :publish-button-always-active="publishButtonAlwaysActive"
              :image-swap="$t('urlImageSwap')"
            />
          </v-col>
        </v-row>
        <!-- end search -->

        <!-- homeContent -->
        <div
          :style="'background-image:url(\''+$t('urlBackground2')+'\');background-size:contain;background-position-y:20em;'"
        >
          <v-row
            align="center"
            class="mt-2"
            justify="center"
          >
            <v-col
              cols="12"
              xl="6"
              lg="9"
              md="12"
            >
              <home-content
                :solidary-display="solidaryDisplay"
                :url-mobile="mobileUrl"
              />
            </v-col>
          </v-row>
        </div>

        <!-- end homeContent -->

        <!-- homeEventList -->
        <v-row>
          <v-col
            cols="12"
            xl="6"
            lg="10"
            md="10"
          >
            <home-event-list />
          </v-col>
        </v-row>
        <!-- end homeEventList -->

        <!-- homeCarpools -->
        <v-row
          align="center"
          class="mt-2"
          justify="center"
        >
          <v-col>
            <home-carpools />
          </v-col>
        </v-row>
        <!-- end homeCarpools -->

        <!-- homeBottom -->
        <div
          :style="'background-image:url(\''+$t('urlBackground3')+'\');background-size:cover;background-position-y:-50em;'"
          class="mt-n8"
        >
          <v-row
            align="center"
            class="mt-4"
            justify="center"
          >
            <v-col
              cols="12"
              lg="10"
              md="12"
              xl="8"
              class="px-14"
            >
              <home-bottom />
            </v-col>
          </v-row>
        </div>
        <!-- end homeBottom -->

        <Cookies />
      </div>
    </v-container>
  </v-main>
</template>

<script>
import {merge} from "lodash";
import Cookies from "@components/utilities/Cookies";
import {messages_en, messages_fr} from "@translations/components/home/Home/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/home/Home/";
import Search from "@components/carpool/search/Search";
import HomeContent from "@components/home/HomeContent";
import HomeEventList from "@components/home/HomeEventList";
import HomeCarpools from "@components/home/HomeCarpools";
import HomeBottom from "@components/home/HomeBottom";



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
    Search,
    HomeContent,
    HomeEventList,
    HomeCarpools,
    HomeBottom,
    Cookies
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    debug: {
      type: Boolean,
      default: false
    },
    position: {
      type: String,
      default: ""
    },
    transitionName: {
      type: String,
      default: ""
    },
    solidaryDisplay: {
      type: Boolean,
      default: false
    },
    eventDisplay: {
      type: Boolean,
      default: false
    },
    displayDeleteAccount: {
      type: Number,
      default: 0
    },
    displayUnsubscribeEmail: {
      type: String,
      default: ""
    },
    communityDisplay: {
      type: Boolean,
      default: false
    },
    publishButtonAlwaysActive:{
      type: Boolean,
      default:false
    },
    // params to add to the publish and search routes
    params: {
      type: Object,
      default: null
    },
    urlMobile: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      snackbar: true,
      displayVerifiedMessage: false,
      mobileUrl: this.urlMobile
    }
  },
  mounted() {
    this.checkVerifiedPhone();
  },
  methods:{
    checkVerifiedPhone() {
      if (this.user !==null && this.user.telephone !== null) {
        this.displayVerifiedMessage = this.user.phoneValidatedDate ? false : true;
      }
    }
  }
};
</script>
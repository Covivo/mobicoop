<template>
  <v-container
    text-center
    fluid
    class="pa-0"
  >
    <v-row v-if="displayVerifiedMessage">
      <v-col class="pa-0">
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

    <!-- Title and subtitle -->
    <div
      :style="'background-image:url(\''+$t('urlBackground')+'\');background-size:contain;width:100%;background-position-y:-1em;'"
    >
      <v-row
        align="center"
        justify="center"
      >
        <v-col
          class="text-center mt-md-n12 pt-md-16 mt-lg-n4 white--text"
          :style="'font-size:1.25rem;line-height:1.25;'"
        >
          <h1
            v-html="$t('title')"
          />
        </v-col>
      </v-row>
      <!-- end Title and subtitle -->

      <!-- search -->
      <v-row
        justify="center"
        class="mt-md-n16 pt-md-16 mt-lg-16 pt-lg-16"
      >
        <v-col class=" mt-md-10 mt-lg-8">
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
      <home-content
        :community-display="communityDisplay"
        :event-display="eventDisplay"
        :solidary-display="solidaryDisplay"
        :url-mobile="mobileUrl"
      />
      <!-- end homeBottom -->
      <Cookies />
    </div>
  </v-container>
</template>

<script>
import {merge} from "lodash";
import Cookies from "@components/utilities/Cookies";
import {messages_en, messages_fr} from "@translations/components/home/Home/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/home/Home/";
import Search from "@components/carpool/search/Search";
import HomeContent from "@components/home/HomeContent";

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
      mobileUrl: this.urlMobile,
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
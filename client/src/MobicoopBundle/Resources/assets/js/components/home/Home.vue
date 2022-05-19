<template>
  <v-container
    aria-label="main"
    text-center
    fluid
    :style="'background-image:url(\''+$t('urlBackground')+'\');background-size:contain;width:100%;'"
    pa-0
  >
    <MSnackInfos
      :active="informativeMessageActive"
      :text="informativeMessageText"
    />
    <v-row v-if="displayVerifiedMessagePhone">
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
            <a
              :href="$t('profileLink')"
              :title="$t('myProfile')"
            >
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

    <v-row v-if="displayVerifiedMessageEmail">
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
            {{ $t('snackbar3') }}
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
            {{ $t('snackbar-account-logout') }}
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

    <v-row
      justify="center"
    >
      <v-col
        class="text-center mt-md-n8 pt-md-16 mt-lg-n4 white--text"
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
      class="mt-5 mt-md-n16 pt-md-16 mt-lg-16 pt-lg-16"
      justify="center"
    >
      <v-col class=" mt-md-10 mt-lg-8">
        <search
          :geo-search-url="geoSearchUrl"
          :user="user"
          :regular="regular"
          :punctual-date-optional="punctualDateOptional"
          :publish-button-always-active="publishButtonAlwaysActive"
          :image-swap="$t('urlImageSwap')"
          :horizontal="searchComponentHorizontal"
          :geo-complete-results-order="geoCompleteResultsOrder"
          :geo-complete-palette="geoCompletePalette"
          :geo-complete-chip="geoCompleteChip"
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
      :user-id="(user && user.id) ? user.id : null"
    />
    <!-- end homeBottom -->
  </v-container>
</template>

<script>
import {merge} from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/home/Home/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/home/Home/";
import Search from "@components/carpool/search/Search";
import HomeContent from "@components/home/HomeContent";
import MSnackInfos from "@components/utilities/MSnackInfos";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  components: {
    Search,
    HomeContent,
    MSnackInfos
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
    },
    informativeMessageActive: {
      type: Boolean,
      default: false
    },
    informativeMessageText: {
      type: String,
      default: null
    },
    searchComponentHorizontal: {
      type: Boolean,
      default: false
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
  },
  data () {
    return {
      snackbar: true,
      displayVerifiedMessagePhone: false,
      mobileUrl: this.urlMobile,
      displayVerifiedMessageEmail: false
    }
  },
  mounted() {
    if (this.user !==null && this.user.validatedDate !== null){
      this.checkVerifiedPhone();
    } else {
      this.checkVerifiedEmail();
    }

  },
  methods:{
    checkVerifiedPhone() {
      if (this.user !==null && this.user.telephone !== null) {
        this.displayVerifiedMessagePhone = this.user.phoneValidatedDate ? false : true;
      }
    },
    checkVerifiedEmail() {
      if (this.user !==null && this.user.email !== null) {
        this.displayVerifiedMessageEmail = this.user.validatedDate ? false : true;
      }
    }
  }
};
</script>

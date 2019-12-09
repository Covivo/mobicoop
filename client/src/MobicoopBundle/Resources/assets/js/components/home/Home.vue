<template>
  <v-content color="secondary">
    <v-container
      text-center
    >
      <v-row v-if="displayVerifiedMessage">
        <v-col>
          <v-snackbar
            v-model="snackbar"
            top
            multi-line
            color="info"
            vertical
          >
            <div>
              {{ $t('snackbar') }}
              <a :href="$t('profileLink')">
                " {{ $t('myProfile') }} "
              </a>
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
      <!-- Title and subtitle -->
      <v-row
        align="center"
        class="mt-5"
        justify="center"
      >
        <v-col
          cols="12"
          xl="6"
          lg="9"
          md="12"
          class="text-center"
        >
          <h1>{{ $t('title') }}</h1>
          <h3 v-html="$t('subtitle')" />
        </v-col>
      </v-row>
      <search
        :geo-search-url="geoSearchUrl"
        :user="user"
        :regular="regular"
        :punctual-date-optional="punctualDateOptional"
      />
      <v-row
        align="center"
        class="mt-5"
        justify="center"
      >
        <v-col
          cols="12"
          xl="6"
          lg="9"
          md="12"
        >
          <home-content
            :community-display="communityDisplay"
            :event-display="eventDisplay"
            :solidary-display="solidaryDisplay"
          />
        </v-col>
      </v-row>
      <Cookies />
    </v-container>
  </v-content>
</template>

<script>
import {merge} from "lodash";
import Cookies from "@components/utilities/Cookies";
import Translations from "@translations/components/home/Home.json";
import TranslationsClient from "@clientTranslations/components/home/Home.json";
import Search from "@components/carpool/search/Search";
import HomeContent from "@components/home/HomeContent";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
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
      type: Boolean,
      default: false
    },
    communityDisplay: {
      type: Boolean,
      default: false
    },
    // params to add to the publish and search routes
    params: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      snackbar: true,
      displayVerifiedMessage: false,
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
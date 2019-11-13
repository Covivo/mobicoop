<template>
  <v-content color="secondary">
    <v-container
      grid-list-md
      text-xs-center
    >
      <v-row v-if="displayVerifiedMessage">
        <v-col>
          <v-snackbar
            v-model="snackbar"
            top
            multi-line
            color="info"
            vertical
            :timeout="0"
          >
            {{ $t('snackbar') }}
            <v-btn
              color="info"
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
          cols="6"
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
        <v-col cols="6">
          <home-content 
            :community-display="communityDisplay"
            :event-display="eventDisplay"
            :solidary-display="solidaryDisplay"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import {merge} from "lodash";
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
    HomeContent
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
    solidaryDisplay: {
      type: Boolean,
      default: false
    },
    eventDisplay: {
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
<template>
  <v-content color="secondary">
    <v-container
      text-xs-center
      grid-list-md
    >
      <!-- Title and subtitle -->
      <v-layout
        v-if="!notitle"
        row
        justify-center
        align-center
        class="mt-5"
      >
        <v-flex
          v-if="notembedded"
          xs6
        >
          <h1>{{ $t('title') }}</h1>
          <h3 v-html="$t('subtitle')" />
        </v-flex>
        <v-flex v-else>
          <h1>{{ $t('title') }}</h1>
          <h3 v-html="$t('subtitle')" />
        </v-flex>
      </v-layout>

      <v-layout
        row
        justify-center
      >
        <v-flex
          v-if="notembedded"
          xs6
        >
          <!--SearchJourney-->
          <search-journey
            :geo-search-url="geoSearchUrl"
            :user="user"
            @change="searchChanged"
          />
        </v-flex>
        <v-flex
          v-else
        >
          <!--SearchJourney-->
          <search-journey
            :geo-search-url="geoSearchUrl"
            :user="user"
            @change="searchChanged"
          />
        </v-flex>
      </v-layout>

      <!-- Buttons -->
      <v-layout
        class="mt-5"
        row
      >
        <v-flex
          xs2
          offset-xs3
        >
          <v-btn
            v-show="!justsearch"
            rounded
            outlined
            disabled
            @click="publish"
          >
            {{ $t('buttons.shareAnAd.label') }}
          </v-btn>
        </v-flex>
        <v-flex xs2>
          <v-btn
            color="success"
            rounded
            :loading="loading"
            :disabled="searchUnavailable"
            @click="search"
          >
            {{ $t('buttons.search.label') }}
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
import moment from "moment";
import {merge} from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/home/HomeSearch.json";
import TranslationsClient from "@clientTranslations/components/home/HomeSearch.json";
import SearchJourney from "@components/carpool/SearchJourney";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    SearchJourney
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    route: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
      default: null
    },
    justsearch: {
      type: Boolean,
      default: false
    },
    notitle: {
      type: Boolean,
      default: false
    },
    notembedded: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      loading: false,
      regular: true,
      date: null,
      origin: null,
      destination: null,
      baseUrl: window.location.origin,
      locale: this.$i18n.locale
    };
  },
  computed: {
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/${this.origin.addressLocality}/${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.computedDateFormat}/resultats`;
    },
    searchUnavailable() {
      return (!this.origin || !this.destination || this.loading == true)
    },
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullNumericDate"))
        : moment(new Date()).format(this.$t("ui.i18n.date.format.fullNumericDate"));
    },
  },
  methods: {
    searchChanged: function (search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.regular = search.regular;
      this.date = search.date;
    },
    search: function () {
      this.loading = true;

      window.location.href = this.urlToCall;
    },
    publish: function () {
      this.loading = true;
      console.error("publish !");
    }
  }
};
</script>
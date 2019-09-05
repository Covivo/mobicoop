<template>
  <v-content color="secondary">
    <v-container
      text-xs-center
      grid-list-md
    >
      <!-- Title and subtitle -->
      <v-layout
        row
        justify-center
        align-center
        class="mt-5"
      >
        <v-flex xs6>
          <h1>{{ $t('title') }}</h1>
          <h3 v-html="$t('subtitle')" />
        </v-flex>
      </v-layout>

      <v-layout
        row
        justify-center
      >
        <v-flex
          xs6
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
import { merge } from "lodash";
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
      // return `${this.baseUrl}/${this.route}/${this.origin.addressLocality}/${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.computedDateFormat}/resultats`;
      return `${this.baseUrl}/${this.route}/Boulevard_d’Austrasie,_Nancy/Rue_d’Arcole,_Sanary-sur-Mer/48.6937223/6.1834097/49.1196964/6.1763552/${this.dateFormated}/resultats`;


    },
    // TODO : DON'T FORGET TO UNCOMMENT
    // searchUnavailable() {
    //   return (!this.origin || !this.destination || this.loading == true)      
    // },
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullNumericDate"))
        : moment(new Date()).format(this.$t("ui.i18n.date.format.fullNumericDate"));
    },
    dateFormated() {
      // return !this.date
      //   ? moment(new Date()).format("YYYYMMDDHHmmss")
      //   : moment(this.date).format("YYYYMMDDHHmmss");
      return moment(new Date('2019-09-30T09:00:00.000Z')).utcOffset("+00:00").format() //@TODO: Uncomment true method
      //TODO : see if we apply GMT or not
    },

  },
  methods: {
    searchChanged: function(search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.regular = search.regular;
      this.date = search.date;
    },
    search: function() {
      this.loading = true;
      
      window.location.href = this.urlToCall;
    },
    publish: function() {
      this.loading = true;
      console.error("publish !");
    }
  }
};
</script>
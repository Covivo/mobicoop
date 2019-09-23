<template>
  <v-content color="secondary">
    <v-container
      grid-list-md
      text-xs-center
    >
      <!-- Title and subtitle -->
      <v-layout
        v-if="!notitle"
        align-center
        class="mt-5"
        justify-center
        row
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
        justify-center
        row
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
            :regular="true"
            @change="searchChanged"
          />
        </v-flex>
      </v-layout>
      
      <!-- Select Time -->
      <v-layout
        row
        justify-center
        align-center
        class="mt-5"
      >
        <v-flex
          xs6
        >
          <v-select
            v-model="time"
            :disabled="regular"
            :items="items"
            label="Heure de départ"
          />
        </v-flex>
      </v-layout>

      <!-- Buttons -->
      <v-layout
        class="mt-5"
        row
      >
        <v-flex
          offset-xs3
          xs2
        >
          <v-btn
            v-show="!justsearch"
            disabled
            outlined
            rounded
            @click="publish"
          >
            {{ $t('buttons.shareAnAd.label') }}
          </v-btn>
        </v-flex>
        <v-flex xs2>
          <v-btn
            :disabled="searchUnavailable"
            :loading="loading"
            color="success"
            rounded
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
      menu: false,
      regular: true,
      date: null,
      time: null,
      origin: null,
      destination: null,
      baseUrl: window.location.origin,
      locale: this.$i18n.locale,
      items: ['00:00', '01:00', '02:00', '03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00']
    };
  },
  computed: {
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/${this.origin.addressLocality}/${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.dateFormated}/resultats`;
      // return `${this.baseUrl}/${this.route}/Boulevard_d’Austrasie,_Nancy/Rue_d’Arcole,_Sanary-sur-Mer/48.6937223/6.1834097/49.1196964/6.1763552/${this.dateFormated}/resultats`;


    },
    searchUnavailable() {
      return (!this.origin || !this.destination || this.loading == true)
    },
    // computedDateFormat() {
    //   moment.locale(this.locale);
    //   return this.date
    //     ? moment(new Date(this.date+":"+this.time+":00Z")).utcOffset("+00:00").format()
    //     : moment(new Date()).utcOffset("+00:00").format();
    // },
    dateFormated() {
      // return !this.date
      //   ? moment(new Date()).format("YYYYMMDDHHmmss")
      //   : moment(this.date).format("YYYYMMDDHHmmss");
      return moment(new Date(this.date+":"+this.time+":00Z")).utcOffset("+00:00").format() //@TODO: Uncomment true method
      //TODO : see if we apply GMT or not
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
      this.regular,


      window.location.href = this.urlToCall;
    },
    publish: function () {
      this.loading = true;
      console.error("publish !");
    }
  }
};
</script>
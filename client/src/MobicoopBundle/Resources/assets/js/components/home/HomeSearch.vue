<template>
  <v-content color="secondary">
    <v-container
      grid-list-md
      text-xs-center
    >
      <!-- Title and subtitle -->
      <v-row
        v-if="!notitle"
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

      <v-row
        justify="center"
      >
        <v-col
          cols="6"
        >
          <!--SearchJourney-->
          <search-journey
            :geo-search-url="geoSearchUrl"
            :user="user"
            @change="searchChanged"
          />
        </v-col>
      </v-row>
      
      <!-- Select Time -->
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="6"
        >
          <v-select
            v-model="time"
            :disabled="regular"
            :items="items"
            label="Heure de départ"
          />
        </v-col>
      </v-row>

      <!-- Buttons -->
      <v-row
        class="mt-5"
        align="center"
        justify="center"
      >
        <v-col
          cols="3"
          offset="2"
        >
          <v-btn
            v-if="isMember"
            disabled
            outlined
            rounded
            @click="publish"
          >
            {{ $t('buttons.shareAnAd.label') }}
          </v-btn>
        </v-col>
        <v-col 
          cols="3"
        >
          <v-btn

            :disabled="searchUnavailable || !isMember"
            :loading="loading"
            color="success"
            rounded
            @click="search"
          >
            {{ $t('buttons.search.label') }}
          </v-btn>
        </v-col>
      </v-row>
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
    notitle: {
      type: Boolean,
      default: false
    },
    // For the community page. If the user is not a member of the community the publish button is not displayed
    isMember: {
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
      return `${this.baseUrl}/${this.route}/${this.origin.addressLocality}/${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.computedDateFormat}/resultats`;
      // return `${this.baseUrl}/${this.route}/Boulevard_d’Austrasie,_Nancy/Rue_d’Arcole,_Sanary-sur-Mer/48.6937223/6.1834097/49.1196964/6.1763552/${this.dateFormated}/resultats`;


    },
    searchUnavailable() {
      return (!this.origin || !this.destination || this.loading == true)
    },
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(new Date(this.date+":"+this.time+":00Z")).utcOffset("+00:00").format()
        : moment(new Date()).utcOffset("+00:00").format();
    },
    dateFormated() {
      // return !this.date
      //   ? moment(new Date()).format("YYYYMMDDHHmmss")
      //   : moment(this.date).format("YYYYMMDDHHmmss");
      return moment(new Date(this.date)).utcOffset("+00:00").format() //@TODO: Uncomment true method
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

      window.location.href = this.urlToCall;
    },
    publish: function () {
      this.loading = true;
      console.error("publish !");
    }
  }
};
</script>
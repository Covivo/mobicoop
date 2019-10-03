<template>
  <v-content color="secondary">
    <v-container
      grid-list-md
      text-xs-center
    >
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
            :init-regular="dataRegular"
            @change="searchChanged"
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
            outlined
            :disabled="searchUnavailable || !logged"
            rounded
            :loading="loading"
            @click="publish"
          >
            {{ $t('buttons.shareAnAd.label') }}
          </v-btn>
        </v-col>
        <v-col 
          cols="3"
        >
          <v-tooltip
            top
            color="info"
          >
            <template v-slot:activator="{ on }">
              <v-btn
                :disabled="searchUnavailable"
                :loading="loading"
                color="success"
                rounded
                v-on="on"
                @click="search"
              >
                {{ $t('buttons.search.label') }}
              </v-btn>
            </template>
            <span>{{ $t('ui.infos.notAvailableYet') }}</span>
          </v-tooltip>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import moment from "moment";
import {merge} from "lodash";
import axios from "axios";
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
    user: {
      type: Object,
      default: null
    }, 
    regular: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: false,
      logged: this.user != "" ? true : false,
      dataRegular: this.regular,
      date: null,
      time: null,
      origin: null,
      destination: null,
      locale: this.$i18n.locale
    };
  },
  computed: {
    searchUnavailable() {
      return (!this.origin || !this.destination || (!this.dataRegular && !this.date) || this.loading == true)
    },
    dateFormated() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.urlDate"))
        : null;
    },
  },
  methods: {
    post: function (path, params, method='post') {
      const form = document.createElement('form');
      form.method = method;
      form.action = window.location.origin+'/'+path;

      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement('input');
          hiddenField.type = 'hidden';
          hiddenField.name = key;
          hiddenField.value = params[key];

          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    searchChanged: function (search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.dataRegular = search.regular;
      this.date = search.date;
    },
    search: function () {
      this.loading = true;
      let params = {
        origin: JSON.stringify(this.origin),
        destination: JSON.stringify(this.destination),
        regular:this.dataRegular?'1':'0',
        date:this.date?this.date:null,
        time:this.time?this.time:null
      };
      this.post(`${this.$t("searchRoute")}`, params);
    },
    publish: function () {
      this.loading = true;
      let params = [];
      let communityId = (this.community) ? "/"+this.community.id : "";
      if (this.origin) {
        params.push("origin="+this.origin.displayedLabel);
        params.push("originLat="+this.origin.latitude);
        params.push("originLon="+this.origin.longitude);
        params.push("originAddressLocality="+this.origin.addressLocality);
      }
      if (this.destination) {    
        params.push("destination="+this.destination.displayedLabel);
        params.push("destinationLat="+this.destination.latitude);
        params.push("destinationLon="+this.destination.longitude);
        params.push("destinationAddressLocality="+this.destination.addressLocality);
      }
      if (this.dataRegular) {
        params.push("regular=1");
      }
      else{
        params.push("regular=0");
      }
      if (this.date) {
        params.push("date="+this.date);
      }
      window.location.href = "/covoiturage/annonce/poster"+communityId+"?"+params.join("&");
    },
  }
};
</script>
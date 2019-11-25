<template>
  <v-content color="secondary">
    <v-container
      grid-list-md
      text-xs-center
    >
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
            :init-destination="destination"
            :punctual-date-optional="punctualDateOptional"
            :show-destination="true"
            @change="searchChanged"
          />
        </v-col>
      </v-row>

      <!-- Buttons -->
      <v-row
        justify="center"
      >
        <v-col
          cols="6"
          class="text-right"
        >
          <v-btn
            v-if="!hidePublish"
            outlined
            :disabled="searchUnavailable || !logged"
            rounded
            :loading="loadingPublish"
            @click="publish"
          >
            {{ $t('buttons.publish.label') }}
          </v-btn>
          <v-btn
            :disabled="searchUnavailable || disableSearch"
            :loading="loadingSearch"
            color="secondary"
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
import Translations from "@translations/components/carpool/search/Search.json";
import TranslationsClient from "@clientTranslations/components/carpool/search/Search.json";
import SearchJourney from "@components/carpool/search/SearchJourney";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
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
    }, 
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    // params to add to the publish and search routes
    params: {
      type: Object,
      default: null
    },
    defaultDestination: {
      type: Object,
      default: null
    },
    disableSearch: {
      type: Boolean,
      default: false
    },
    hidePublish: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      loadingSearch: false,
      loadingPublish: false,
      logged: this.user ? true : false,
      dataRegular: this.regular,
      date: null,
      time: null,
      origin: null,
      destination: this.defaultDestination,
      locale: this.$i18n.locale
    };
  },
  computed: {
    searchUnavailable() {
      return (!this.origin || !this.destination || (!this.dataRegular && !this.date && !this.punctualDateOptional))
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
      // form.target= "_blank";

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
      this.loadingSearch = true;
      let lParams = {
        origin: JSON.stringify(this.origin),
        destination: JSON.stringify(this.destination),
        regular:this.dataRegular,
        date:this.date?this.date:null,
        time:this.time?this.time:null,
        ...this.params
      };
      this.post(`${this.$t("buttons.search.route")}`, lParams);
    },
    publish: function () {
      this.loadingPublish = true;
      let lParams = {
        origin: JSON.stringify(this.origin),
        destination: JSON.stringify(this.destination),
        regular:this.dataRegular,
        date:this.date?this.date:null,
        time:this.time?this.time:null,
        ...this.params
      };
      this.post(`${this.$t("buttons.publish.route")}`, lParams);
    },
  }
};
</script>

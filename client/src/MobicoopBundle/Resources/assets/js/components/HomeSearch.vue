<template>
  <v-content color="secondary">
    <v-container
      text-xs-center
      grid-list-md
      fluid
      class="pt-0 px-0"
      :class="this.backgroundColor+' '+this.textColor"
    >
      <div
        class="backgroundImage"
        :style="style"
      >
        <!-- Title and subtitle -->
        <v-layout
          justify-center
          align-center
          class="mt-5"
        >
          <v-flex xs6>
            <h1>{{ $t('title') }}</h1>
            <h3 v-html="$t('subtitle')" />
          </v-flex>
        </v-layout>

        <!-- Geocompletes -->
        <v-layout
          class="mt-5"
          align-center
        >
          <v-flex
            xs2
            offset-xs3
          >
            <GeoComplete
              :url="geoSearchUrl"
              :label="labelOrigin"
              :token="user ? user.geoToken : ''"
              @address-selected="originSelected"
            />
          </v-flex>
          <v-flex
            class="text-center"
            xs2
          >
            <v-tooltip right>
              <template v-slot:activator="{ on }">
                <v-btn
                  text
                  icon
                  @click="swap"
                >
                  <img
                    src="images/PictoInterchanger.svg"
                    :alt="$t('swap.alt')"
                    v-on="on"
                  >
                </v-btn>
              </template>
              <span>{{ $t('swap.help') }}</span>
            </v-tooltip>
          </v-flex>
          <v-flex xs2>
            <GeoComplete
              :url="geoSearchUrl"
              :label="labelDestination"
              :token="user ? user.geoToken : ''"
              @address-selected="destinationSelected"
            />
          </v-flex>
        </v-layout>

        <!-- Switch -->
        <v-layout
          class="mt-5"
          align-center
          fill-height
        >
          <v-flex
            xs1
            offset-xs3
          >
            {{ $t('switch.label') }}
          </v-flex>
          <v-flex
            xs1
            row
            class="text-right"
          >
            <v-switch
              v-model="regular"
              inset
              color="success"
            />
            <v-tooltip right>
              <template v-slot:activator="{ on }">
                <v-icon v-on="on">
                  mdi-help-circle-outline
                </v-icon>
              </template>
              <span>{{ $t('switch.help') }}</span>
            </v-tooltip>
          </v-flex>
        </v-layout>

        <!-- Datepicker -->
        <v-layout
          class="mt-5"
          align-center
        >
          <v-flex
            xs2
            offset-xs3
          >
            <v-menu
              v-model="menu"
              :close-on-content-click="false"
              full-width
            >
              <template v-slot:activator="{ on }">
                <v-text-field
                  :value="computedDateFormat"
                  clearable
                  :label="$t('datePicker.label')"
                  readonly
                  :messages="$t('ui.form.optional')"
                  v-on="on"
                  @click:clear="clearDate"
                />
              </template>
              <v-date-picker
                v-model="date"
                header-color="primary"
                color="secondary"
                :locale="locale"
                @input="menu=false"
              />
            </v-menu>
          </v-flex>
        </v-layout>

        <!-- Buttons -->
        <v-layout
          class="mt-5"
          align-center
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
              :color="this.colorButtonSearch"
              rounded
              :disabled="searchUnavailable"
              @click="search"
            >
              {{ $t('buttons.search.label') }}
            </v-btn>
          </v-flex>
        </v-layout>
      </div>
    </v-container>
  </v-content>
</template>

<script>
import moment from "moment";
import GeoComplete from "./GeoComplete";

import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/HomeSearch.json";
import TranslationsClient from "@clientTranslations/components/HomeSearch.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    GeoComplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: null
    },
    route: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
      default: null
    },
    backgroundColor: { // Optional for style binding in container (vuetify class expected like primary)
      type: String,
      default: ""
    },
    textColor: { // Optional for style binding in container and other component (vuetify class expected like white--color)
      type: String,
      default: ""
    },
    // Optionnal props for styling (-> computed)
    backgroundImage: {
      type: String,
      default: ""
    },
    colorButtonSearch:{
      type: String,
      default: "success"
    }
  },
  data() {
    return {
      regular: false,
      date: null,
      menu: false,
      labelOrigin: this.$t("origin"),
      labelDestination: this.$t("destination"),
      locale: this.$i18n.locale,
      origin: null,
      destination: null,
      baseUrl: window.location.origin
    };
  },
  computed: {
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    dateFormated() {
      return !this.date
        ? moment(new Date()).format("YYYYMMDDHHmmss")
        : moment(this.date).format("YYYYMMDDHHmmss");
    },
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/origine/destination/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.dateFormated}/resultats`;
    },
    searchUnavailable() {
      return !this.origin || !this.destination;
    },
    style(){
      let styleBackgroundImage = "";
      (this.backgroundImage!=='') ? styleBackgroundImage = "background-image:url("+this.backgroundImage+")" : styleBackgroundImage = '';
      return styleBackgroundImage;
    }
  },
  methods: {
    originSelected: function(address) {
      this.origin = address;
    },
    destinationSelected: function(address) {
      this.destination = address;
    },
    swap: function() {
      console.error("swap !");
    },
    search: function() {
      window.location.href = this.urlToCall;
    },
    publish: function() {
      console.error("publish !");
    },
    clearDate() {
      this.date = null;
    }
  }
};
</script>
<style lang="scss" scoped>
.backgroundImage{
  background-size: cover;
}
</style>

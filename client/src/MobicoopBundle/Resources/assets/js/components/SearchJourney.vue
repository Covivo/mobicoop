<template>
  <v-content color="secondary">
    <v-container
      text-xs-center
      grid-list-md
      fluid
    >
      <!--Role-->
      <v-layout
        v-if="displayRoles"
        class="mt-5"
        align-center
        fill-height
      >
        <v-flex
          xs1
          offset-xs3
        >
          Driver
        </v-flex>
        <v-flex
          xs1
          row
          class="text-right"
        >
          <v-switch
            v-model="driver"
            inset
            color="success"
            @change="switched"
          />
        </v-flex>

        <v-flex
          xs1
          offset-xs3
        >
          Passenger
        </v-flex>
        <v-flex
          xs1
          row
          class="text-right"
        >
          <v-switch
            v-model="passenger"
            inset
            color="success"
            @change="switched"
          />
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
            @change="switched"
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
              @change="dateChanged"
            />
          </v-menu>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
import moment from "moment";
import GeoComplete from "./GeoComplete";

import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/SearchJourney.json";
import TranslationsClient from "@clientTranslations/components/SearchJourney.json";

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
      default: ""
    },
    user: {
      type: Object,
      default: null
    },
    displayRoles: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      regular: false,
      date: null,
      menu: false,
      passenger: false,
      driver: false,
      labelOrigin: this.$t("origin"),
      labelDestination: this.$t("destination"),
      locale: this.$i18n.locale,
      origin: null,
      destination: null
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
    }
  },
  methods: {
    originSelected: function(address) {
      this.origin = address;
      this.emitEvent();
    },
    destinationSelected: function(address) {
      this.destination = address;
      this.emitEvent();
    },
    swap: function() {
      this.emitEvent();
    },
    switched: function(){
      this.emitEvent();
    },
    dateChanged: function() {
      this.emitEvent();
    },
    emitEvent: function(){
      console.error(this.dateFormated)
      this.$emit("change", {
        origin: this.origin,
        destination: this.destination,
        regular: this.regular,
        date: this.dateFormated,
        passenger: this.passenger,
        driver: this.driver
      });
    },
    clearDate() {
      this.date = null;
      this.emitEvent();
    }
  }
};
</script>
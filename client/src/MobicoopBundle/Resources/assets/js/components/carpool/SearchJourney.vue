<template>
  <v-content color="secondary">
    <v-container
      fluid
    >
      <!--Role-->
      <v-row
        v-if="displayRoles"
        align="center"
        justify="center"
        dense
      >
        <v-col
          cols="2"
        >
          {{ $t('switch.driver.label') }}
        </v-col>
        <v-col
          cols="1"
        >
          <v-switch
            v-model="driver"
            color="success"
            inset
            @change="switched"
          />
        </v-col>
        <v-col
          cols="2"
        >
          {{ $t('switch.passenger.label') }}
        </v-col>
        <v-col
          cols="2"
        >
          <v-switch
            v-model="passenger"
            inset
            color="success"
            @change="switched"
          />
        </v-col>
      </v-row>

      <!-- Geocompletes -->
      <v-row
        align="center"
        dense
      >
        <v-col
          cols="5"
        >
          <GeoComplete
            :url="geoSearchUrl"
            :label="labelOrigin"
            :token="user ? user.geoToken : ''"
            required
            :required-error="requiredErrorOrigin"
            :init-address="customInitOriginAddress"
            @address-selected="originSelected"
          />
        </v-col>
        <v-col
          cols="2"
        >
          <v-tooltip right>
            <template v-slot:activator="{ on }">
              <v-btn
                text
                icon
                @click="swap"
              >
                <v-icon>mdi-swap-horizontal</v-icon>
              </v-btn>
            </template>
            <span>{{ $t('swap.help') }}</span>
          </v-tooltip>
        </v-col>
        <v-col 
          cols="5"
        >
          <GeoComplete
            :url="geoSearchUrl"
            :label="labelDestination"
            :token="user ? user.geoToken : ''"
            required
            :required-error="requiredErrorDestination"
            :init-address="customInitDestinationAddress"
            @address-selected="destinationSelected"
          />
        </v-col>
      </v-row>

      <!-- Switch -->
      <v-row
        align="center"
        no-gutters
      >
        <v-col
          cols="3"
          align="left"
        >
          {{ $t('switch.regular.label') }}
        </v-col>
        <v-col
          cols="1"
        >
          <v-switch
            v-model="regular"
            inset
            hide-details
            class="mt-0"
            color="success"
            @change="switched"
          />
        </v-col>
        <v-col
          cols="1"
          align="left"
        >
          <v-tooltip right>
            <template v-slot:activator="{ on }">
              <v-icon v-on="on">
                mdi-help-circle-outline
              </v-icon>
            </template>
            <span>{{ $t('switch.regular.help') }}</span>
          </v-tooltip>
        </v-col>
      </v-row>

      <!-- Datepicker -->
      <v-row
        align="center"
        dense
      >
        <v-col
          cols="5"
        >
          <v-menu
            v-model="menu"
            :close-on-content-click="false"
            full-width
            offset-y
            min-width="290px"
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
              no-title
              @input="menu=false"
              @change="dateChanged"
            />
          </v-menu>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import moment from "moment";
import GeoComplete from "@components/utilities/GeoComplete";

import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/SearchJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/SearchJourney.json";

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
    user: {
      type: Object,
      default: null
    },
    displayRoles: {
      type: Boolean,
      default: false
    },
    initOutwardDate: String,
    initOriginAddress: {
      type: Object,
      default: null
    },
    initDestinationAddress: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      date: null,
      regular: false,
      menu: false,
      passenger: false,
      driver: false,
      labelOrigin: this.$t("origin.label"),
      labelDestination: this.$t("destination.label"),
      requiredErrorOrigin: this.$t("origin.error"),
      requiredErrorDestination: this.$t("destination.error"),
      locale: this.$i18n.locale,
      origin: null,
      destination: null,
      customInitOriginAddress: null,
      customInitDestinationAddress: null,
    };
  },
  computed: {
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
  },
  watch: {
    initOutwardDate() {
      this.date = this.initOutwardDate;
    },
    initOriginAddress() {
      this.customInitOriginAddress = this.initOriginAddress;
      this.origin = this.initOriginAddress;
    },
    initDestinationAddress() {
      this.customInitDestinationAddress = this.initDestinationAddress;
      this.destination = this.initDestinationAddress;
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
      let tempOriginAddress = this.customInitOriginAddress;
      this.origin = this.customInitDestinationAddress;
      this.customInitOriginAddress = this.customInitDestinationAddress;
      this.destination = tempOriginAddress;
      this.customInitDestinationAddress = tempOriginAddress;
      this.emitEvent();
    },
    switched: function(){
      this.emitEvent();
    },
    dateChanged: function() {
      this.emitEvent();
    },
    emitEvent: function(){
      this.$emit("change", {
        origin: this.origin,
        destination: this.destination,
        regular: this.regular,
        date: this.date,
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
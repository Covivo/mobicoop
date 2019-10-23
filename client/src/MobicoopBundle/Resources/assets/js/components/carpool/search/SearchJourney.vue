<template>
  <v-content color="secondary">
    <v-container
      fluid
    >
      <v-form
        ref="form"
        v-model="valid"
      >
        <!--Role-->
        <v-row
          v-if="displayRoles"
          align="center"
          justify="center"
          dense
        >
          <v-col
            cols="12"
          >
            <v-row
              align="center"
              justify="space-around"
              dense
            >
              <v-radio-group
                v-model="role"
                row
                :disabled="solidaryAd"
                @change="roleChanged"
              >
                <v-radio
                  :value="1"
                  :label="$t('radio.driver.label')"
                  color="success"
                />
                <v-radio
                  :value="2"
                  :label="$t('radio.passenger.label')"
                  color="success"
                />
                <v-radio
                  :value="3"
                  :label="$t('radio.both.label')"
                  color="success"
                />
              </v-radio-group>
            </v-row>
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
              :init-address="customInitOrigin"
              @address-selected="originSelected"
            />
          </v-col>
          <v-col
            cols="2"
          >
            <v-tooltip 
              color="info"
              right
            >
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
              :init-address="customInitDestination"
              @address-selected="destinationSelected"
            />
          </v-col>
        </v-row>

        <!-- Frequency switch -->
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
            <v-tooltip
              color="info"
              right
            >
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
              <!-- Here we use a little trick to display error message, 
              as validation rules on a readonly component works only after update of the value...
              If we just click in and out the error message does not appear.
              We use a combination of error, error-messages and blur -->
              <template v-slot:activator="{ on }">
                <v-text-field
                  v-show="regular ? false : true"
                  :value="computedDateFormat"
                  clearable
                  :label="$t('outwardDate.label')"
                  readonly
                  :disabled="regular"
                  :error="!date && regular && outwardDateClicked"
                  :error-messages="checkOutwardDate"
                  v-on="on"
                  @click:clear="clearDate"
                  @blur="outwardDateBlur"
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
      </v-form>
    </v-container>
  </v-content>
</template>

<script>
import moment from "moment";
import GeoComplete from "@components/utilities/GeoComplete";

import { merge } from "lodash";
import Translations from "@translations/components/carpool/search/SearchJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/search/SearchJourney.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
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
    initOutwardDate: {
      type: String,
      default: null
    },
    initOrigin: {
      type: Object,
      default: null
    },
    initDestination: {
      type: Object,
      default: null
    },
    initRegular: {
      type: Boolean,
      default: true
    }, 
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    solidaryAd: {
      type: Boolean,
      default: false
    }    
  },
  data() {
    return {
      date: this.initOutwardDate,
      outwardDateClicked: false,
      menu: false,
      regular: this.initRegular,
      role: this.solidaryAd ? 1 : 3,
      passenger: true,
      driver: true,
      labelOrigin: this.$t("origin.label"),
      labelDestination: this.$t("destination.label"),
      requiredErrorOrigin: this.$t("origin.error"),
      requiredErrorDestination: this.$t("destination.error"),
      requiredErrorOutwardDate: this.$t("outwardDate.error"),
      locale: this.$i18n.locale,
      origin: this.initOrigin,
      destination: this.initDestination,
      customInitOrigin: (this.initOrigin)?this.initOrigin:null,
      customInitDestination: (this.initDestination)?this.initDestination:null,
      valid: false
    };
  },
  computed: {
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullDate"))
        : null;
    },
    checkOutwardDate() {
      if (this.outwardDateClicked && !this.regular && !this.date && !this.punctualDateOptional) {
        return this.requiredErrorOutwardDate;
      }
      return null;
    }
  },
  watch: {
    initOutwardDate() {
      this.date = this.initOutwardDate;
    },
    initOrigin() {
      this.customInitOrigin = this.initOrigin;
      this.origin = this.initOrigin;
    },
    initDestination() {
      this.customInitDestination = this.initDestination;
      this.destination = this.initDestination;
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
      let destinationBuffer = this.destination;
      this.destination = this.origin;
      this.customInitDestination = this.origin;
      this.origin = destinationBuffer
      this.customInitOrigin = destinationBuffer
      this.emitEvent();
    },
    switched: function(){
      this.emitEvent();
    },
    dateChanged: function() {
      this.emitEvent();
    },
    roleChanged: function() {
      this.driver = true;
      this.passenger = true;
      if (this.role == 1) {
        this.driver = true;
        this.passenger = false;
      } else if (this.role == 2) {
        this.driver = false;
        this.passenger = true;
      }
      this.emitEvent();
    },
    emitEvent: function(){
      this.$emit("change", {
        origin: this.origin,
        destination: this.destination,
        regular: this.regular,
        date: this.date,
        passenger: this.passenger,
        driver: this.driver,
        valid: this.valid
      });
    },
    clearDate() {
      this.date = null;
      this.emitEvent();
    },
    outwardDateBlur() {
      this.outwardDateClicked = true;
    }
  }
};
</script>
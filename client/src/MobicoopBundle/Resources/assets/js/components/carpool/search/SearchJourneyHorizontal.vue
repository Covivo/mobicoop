<template>
  <div fluid>
    <v-row
      justify="center"
      dense
    >
      <v-col cols="9">
        <v-card class="px-5 py-0">
          <v-form
            ref="form"
            v-model="valid"
          >
            <v-row
              align="center"
              dense
            >
              <v-col
                cols="4"
                lg="3"
                class="ma-0"
              >
                <GeoComplete
                  id="from"
                  :url="geoSearchUrl"
                  :label="labelOrigin"
                  alternative-label="origin"
                  :token="user ? user.token : ''"
                  required
                  :show-required="showRequired"
                  :required-error="requiredErrorOrigin"
                  :prepend-icon="prependIconOrigin"
                  @address-selected="originSelected"
                />
              </v-col>
              <v-col
                cols="4"
                lg="3"
                class="ma-0"
              >
                <GeoComplete
                  id="to"
                  :url="geoSearchUrl"
                  :label="labelDestination"
                  alternative-label="destination"
                  :token="user ? user.token : ''"
                  required
                  :show-required="showRequired"
                  :required-error="requiredErrorDestination"
                  :prepend-icon="prependIconDestination"
                  @address-selected="destinationSelected"
                />
              </v-col>
              <v-col
                cols="4"
                lg="3"
              >
                <v-menu
                  v-model="menu"
                  :close-on-content-click="false"
                  offset-y
                  min-width="290px"
                >
                  <!-- Here we use a little trick to display error message,
              as validation rules on a readonly component works only after update of the value...
              If we just click in and out the error message does not appear.
              We use a combination of error, error-messages and blur -->
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      id="date"
                      :value="computedDateFormat"
                      clearable
                      :label="$t('outwardDate.label') + (showRequired ? ' *' : '')"
                      readonly
                      :disabled="regular"
                      :error="!date && regular && outwardDateClicked"
                      :error-messages="checkOutwardDate"
                      :prepend-inner-icon="prependIconOutwardDate"
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
                    first-day-of-week="1"
                    :min="nowDate"
                    @input="menu=false"
                    @change="dateChanged"
                  />
                </v-menu>
              </v-col>
              <v-col
                cols="4"
                lg="2"
              >
                <v-switch
                  v-model="regular"
                  inset
                  hide-details
                  color="secondary"
                  class="mt-0"
                  :label="$t('switch.regular.label')"
                  @change="switched"
                />
              </v-col>
              <v-col
                cols="8"
                lg="1"
                class="text-left"
              >
                <v-btn
                  :disabled="searchUnavailable || disableSearch"
                  :loading="loadingSearch"
                  color="secondary"
                  rounded
                  @click="search"
                >
                  <v-icon>mdi-magnify</v-icon>
                </v-btn>                
              </v-col>
            </v-row>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import moment from "moment";
import GeoComplete from "@components/utilities/GeoComplete";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/search/SearchJourney/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
    initRegular: {
      type: Boolean,
      default: true
    }, 
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    showRequired: {
      type: Boolean,
      default: false
    },
    searchUnavailable: {
      type: Boolean,
      default: false
    },
    disableSearch: {
      type: Boolean,
      default: false
    },
    prependIconOrigin:{
      type: String,
      default: "mdi-tooltip-account"
    },
    prependIconDestination:{
      type: String,
      default: "mdi-navigation"
    },
    prependIconOutwardDate:{
      type: String,
      default: "mdi-calendar"
    }    
  },
  data() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      date: null,
      outwardDateClicked: false,
      menu: false,
      regular: this.initRegular,
      role: 3,
      passenger: true,
      driver: true,
      labelOrigin: this.$t("origin.label"),
      labelDestination: this.$t("destination.label"),
      requiredErrorOrigin: this.$t("origin.error"),
      requiredErrorDestination: this.$t("destination.error"),
      requiredErrorOutwardDate: this.$t("outwardDate.error"),
      origin: null,
      destination: null,
      valid: false,
      nowDate : new Date().toISOString().slice(0,10),
      loadingSearch:false
    };
  },
  computed: {
    computedDateFormat() {
      return this.date
        ? moment(this.date).format(this.$t("fullDate"))
        : null;
    },
    checkOutwardDate() {
      if (this.outwardDateClicked && !this.regular && !this.date && !this.punctualDateOptional) {
        return this.requiredErrorOutwardDate;
      }
      return null;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
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
    switched: function(){
      this.date = null;
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
    },
    search(){
      this.$emit('search');
    }
  }
};
</script>
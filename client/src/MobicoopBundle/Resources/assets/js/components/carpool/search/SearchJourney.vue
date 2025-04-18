<template>
  <div fluid>
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
              :disabled="solidaryExclusiveAd"
              @change="roleChanged"
            >
              <v-radio
                v-if="hasDriverRoleEnabled"
                :value="1"
                :label="$t('radio.driver.label')"
                color="secondary"
              />
              <v-radio
                :value="2"
                :label="$t('radio.passenger.label')"
                color="secondary"
              />
              <v-radio
                v-if="hasBothRoleEnabled"
                :value="3"
                :label="$t('radio.both.label')"
                color="secondary"
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
          cols="12"
          md="5"
        >
          <geocomplete
            v-show="showOrigin"
            id="labelOrgin"
            :uri="geoSearchUrl"
            :results-order="geoCompleteResultsOrder"
            :palette="geoCompletePalette"
            :chip="geoCompleteChip"
            :label="labelOrigin"
            :aria-label="ariaLabelOrgin"
            required
            aria-invalid="true"
            :address="customInitOrigin"
            @address-selected="originSelected"
          />
        </v-col>
        <v-col
          cols="12"
          md="2"
          class="text-center"
        >
          <v-tooltip
            v-if="showOrigin && showDestination"
            color="info"
            top
          >
            <template v-slot:activator="{ on }">
              <v-btn
                :aria-label="$t('swap.alt')"
                text
                icon
                @click="swap"
                v-on="on"
              >
                <v-icon v-if="!imageSwap">
                  mdi-swap-horizontal
                </v-icon>
                <v-img
                  v-else
                  :alt="$t('swap.alt')"
                  :src="imageSwap"
                />
              </v-btn>
            </template>
            <span>{{ $t('swap.help') }}</span>
          </v-tooltip>
        </v-col>
        <v-col
          cols="12"
          md="5"
        >
          <geocomplete
            v-show="showDestination"
            id="labelDestination"
            :uri="geoSearchUrl"
            :results-order="geoCompleteResultsOrder"
            :palette="geoCompletePalette"
            :chip="geoCompleteChip"
            :label="labelDestination"
            :aria-label="ariaLabelDestination"
            required
            aria-invalid="true"
            :address="customInitDestination"
            @address-selected="destinationSelected"
          />
        </v-col>
      </v-row>

      <!-- Frequency switch -->
      <v-row
        v-if="showOrigin && showDestination"
        id="frenquencySwitch"
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
          class="ma-2 d-flex flex-row align-start"
        >
          <v-switch
            id="switch"
            v-model="regular"
            role="switch"
            inset
            hide-details
            class="mt-0 mr-5 mb-5"
            :aria-label="$t('switch.aria-label')"
            :color="switchColor"
            @change="switched"
          />
          <v-tooltip
            color="info"
            right
            role="tooltip"
          >
            <template v-slot:activator="{ on }">
              <v-icon
                aria-hidden="false"
                v-on="on"
              >
                mdi-help-circle-outline
              </v-icon>
            </template>
            <span v-if="regular">{{ $t('switch.regular.help') }}</span>
            <span v-else>{{ $t('switch.punctual.help') }}</span>
          </v-tooltip>
        </v-col>
      </v-row>

      <!-- Datepicker -->
      <v-row
        align="center"
      >
        <v-col
          cols="12"
          md="5"
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
                v-show="regular ? false : true"
                id="date"
                :value="computedDateFormat"
                clearable
                :label="dateTimePicker ? ($t('outwardDate.altLabel') + (showRequired ? ' *' : '')) : ($t('outwardDate.label') + (showRequired ? ' *' : ''))"
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
              v-if="showDate"
              v-model="date"
              no-title
              header-color="primary"
              color="secondary"
              :locale="locale"
              :min="nowDate"
              first-day-of-week="1"
              @input="switchTime"
              @change="dateChanged"
            />
            <v-time-picker
              v-if="showTime && menu"
              v-model="time"
              format="24hr"
              no-title
              color="secondary"
              :close-on-content-click="false"
              @change="timeChanged"
              @click:minute="setTime"
            />
          </v-menu>
        </v-col>
      </v-row>
    </v-form>
  </div>
</template>

<script>
import moment from "moment";
import Geocomplete from "@components/utilities/geography/Geocomplete";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/search/SearchJourney/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu': messages_eu
    }
  },
  components: {
    Geocomplete
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
    solidaryExclusiveAd: {
      type: Boolean,
      default: false
    },
    showRequired: {
      type: Boolean,
      default: false
    },
    showDestination: {
      type: Boolean,
      default: true
    },
    showOrigin: {
      type: Boolean,
      default: true
    },
    imageSwap:{
      type:String,
      default:""
    },
    initRole: {
      type: Number,
      default: null
    },
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
    dateTimePicker: {
      type: Boolean,
      default: false
    },
    initOutwardTime: {
      type: String,
      default: null
    },
    switchColor: {
      type: String,
      default: 'secondary'
    },
    bothRoleEnabled: {
      type: Boolean,
      default: true
    },
    driverRoleEnabled: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      locale: null,
      date: this.initOutwardDate,
      outwardDateClicked: false,
      menu: false,
      showDate:true,
      showTime:false,
      time: this.initOutwardTime,
      dateTime: (this.initOutwardDate && this.initOutwardTime) ? this.initOutwardDate+' '+this.initOutwardTime : null,
      regular: this.initRegular,
      role: this.initRole ? this.initRole : (this.solidaryExclusiveAd ? 1 : this.bothRoleEnabled ? 3 : 1),
      passenger: this.initRole == 2 ? true : ((this.initRole == 3 && this.bothRoleEnabled) || (this.initRole == null && !this.solidaryExclusiveAd) ? true : false),
      driver: this.initRole == 1 ? true : ((this.initRole == 3 && this.bothRoleEnabled) || this.initRole == null ? true : false),
      labelOrigin: this.$t("origin.label"),
      labelDestination: this.$t("destination.label"),
      requiredErrorOrigin: this.$t("origin.error"),
      requiredErrorDestination: this.$t("destination.error"),
      requiredErrorOutwardDate: this.$t("outwardDate.error"),
      origin: this.determineOrigin(),
      destination: this.initDestination,
      customInitOrigin: this.determineOrigin(),
      customInitDestination: (this.initDestination)?this.initDestination:null,
      valid: false,
      nowDate: new Date().toISOString().slice(0,10),
      ariaLabelDestination : this.$t('ariaLabelDestination'),
      ariaLabelOrgin: this.$t('ariaLabelOrgin'),
      hasDriverRoleEnabled: this.driverRoleEnabled,
      hasBothRoleEnabled: this.bothRoleEnabled,

    };
  },
  computed: {
    computedDateFormat() {
      if (this.dateTimePicker) {
        return this.dateTime ? moment.utc(this.dateTime, this.$t('defaultDatetime')).format(this.$t("fullDateTime")) : null;
      } else {
        return this.date ? moment.utc(this.date, this.$t('defaultDate')).format(this.$t("fullDate")) : null;
      }
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
    initOutwardTime() {
      this.time = this.initOutwardTime;
    },
    initOrigin() {
      this.customInitOrigin = this.initOrigin;
      this.origin = this.initOrigin;
    },
    initDestination() {
      this.customInitDestination = this.initDestination;
      this.destination = this.initDestination;
    },
  },
  created() {
    this.setMomentLocale();
    switch (this.type) {
    case 'date':
      this.showDate = true;
      break;
    case 'time':
      this.showTime = true;
      break;
    default:
      this.showDate = true;
    }
  },
  beforeUpdate() {
    this.setMomentLocale();
  },
  methods: {
    setMomentLocale() {
      this.locale = localStorage.getItem("X-LOCALE");
      moment.locale(this.locale);
    },
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
    timeChanged: function() {
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
        time: this.time,
        passenger: this.passenger,
        driver: this.driver,
        valid: this.valid
      });
    },
    clearDate() {
      this.date = null;
      this.time = null;
      this.emitEvent();
    },
    outwardDateBlur() {
      this.outwardDateClicked = true;
    },
    switchTime() {
      if (!this.dateTimePicker) {
        this.menu = false;
        this.formatDate();
      } else {
        this.showDate = false;
        this.showTime = true;
      }
    },
    setTime() {
      this.menu = false;
      this.showDate = true;
      this.showTime = false;
      this.formatDate();
    },
    formatDate() {
      if (this.dateTimePicker) {
        this.dateTime = (this.date && this.time) ? this.date+' '+this.time : null;
      }
    },
    determineOrigin(){
      if(this.initOrigin){
        return this.initOrigin;
      }

      if(this.user && this.user.homeAddress && this.user.homeAddress.latitude && this.user.homeAddress.longitude){
        return this.user.homeAddress
      }

      return null;
    }
  }
};
</script>

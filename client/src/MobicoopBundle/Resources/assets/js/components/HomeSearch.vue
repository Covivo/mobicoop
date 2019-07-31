<template>
  <v-content color="secondary">
    <v-container
      text-xs-center
      grid-list-md
      fluid
    >
      <!-- Title and subtitle -->
      <v-layout
        justify-center
        align-center
        class="mt-5"
      >
        <v-flex xs6>
          <h1>
            {{ $t('title') }}
          </h1>
          <h3
            v-html="$t('subtitle')"
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
          xs1
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
            max-width="290"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="computedDateFormat"
                clearable
                :label="$t('datePicker.label')"
                readonly
                :messages="$t('ui.form.optional')"
                v-on="on"
              />
            </template>
            <v-date-picker
              v-model="date"
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
            @click="publish"
          >
            {{ $t('buttons.shareAnAd.label') }}
          </v-btn>
        </v-flex>
        <v-flex xs2>
          <v-btn
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
import Translations from "../../../translations/components/HomeSearch.json";
import GeoComplete from "./GeoComplete";


export default {
  i18n: {
    messages: Translations
  },
  components: {
    GeoComplete
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
    }
  },
  data () {
    return {
      regular: false,
      date: null,
      menu: false,
      labelOrigin: this.$t('origin'),
      labelDestination: this.$t('destination'),
      locale: this.$i18n.locale,
      origin: {},
      destination: {},
      baseUrl: window.location.origin,
    }
  },
  computed: {
    computedDateFormat () {
      moment.locale(this.locale);
      return this.date ? moment(this.date).format(this.$t('ui.i18n.date.format.fullDate')) : ''
    },
    
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/nancy/metz/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.dateFormated()}/resultats`;
    }
  },
  methods: {
    originSelected: function(address) {
      this.origin = address
    },
    destinationSelected: function(address) {
      this.destination = address
    },
    dateFormated() {
      return moment(new Date).format("YYYYMMDDHHmmss");
    },
    swap: function() {
      console.error('swap !')
    },
    search: function() {
      window.location.href = this.urlToCall
    },
    publish: function() {
      console.error('publish !')
    },

  }
  
}
</script>
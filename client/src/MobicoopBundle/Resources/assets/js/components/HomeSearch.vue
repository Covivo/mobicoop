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
        <v-flex xs8>
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
          xs3
          offset-xs2
        >
          <GeoComplete
            :url="geoSearchUrl" 
            :label="labelOrigin"
          />
        </v-flex>
        <v-flex
          class="text-center"
          xs2
        >
          <img
            src="images/PictoInterchanger.svg"
            alt="Intervertir origin et destination"
          >
        </v-flex>
        <v-flex xs3>
          <GeoComplete
            :url="geoSearchUrl" 
            :label="labelDestination"
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
          xs2
          offset-xs2
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
          xs3
          offset-xs2
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
          offset-xs2
        >
          <v-btn
            rounded
            outlined
          >
            {{ $t('buttons.shareAnAd.label') }}
          </v-btn>
        </v-flex>
        <v-flex xs2>
          <v-btn
            color="success"
            rounded
            @click="test"
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

    }
  },
  computed: {
    computedDateFormat () {
      moment.locale(this.locale);
      return this.date ? moment(this.date).format(this.$t('ui.i18n.date.format.fullDate')) : ''
    }
  },
  methods: {
    test () {
      console.error(this.date)
    }
  }
  
}
</script>
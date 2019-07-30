<template>
  <v-content color="secondary">
    <v-container
      text-xs-center
      grid-list-md
      fluid
    >
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
          xs1
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
      <v-layout
        class="mt-5"
        align-center
      >
        <v-flex
          xs2
          offset-xs2
        >
          <p>{{ $t('switch.label') }}</p>
        </v-flex>
        <v-flex
          xs2
          row
          class="text-left"
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
            :nudge-right="40"
            transition="scale-transition"
            offset-y
            full-width
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="date"
                :label="$t('datePicker.label')"
                readonly
                :messages="$t('ui.form.optional')"
                v-on="on"
              />
            </template>
            <v-date-picker
              v-model="date"
              scrollable
              :locale="locale"
            >
              <v-spacer />
              <v-btn
                text
                color="primary"
                @click="menu = false"
              >
                {{ $t('ui.button.cancel') }}
              </v-btn>
              <v-btn
                text
                color="primary"
                @click="$refs.dialog.save(date)"
              >
                {{ $t('ui.button.valid') }}
              </v-btn>
            </v-date-picker>
          </v-menu>
        </v-flex>
      </v-layout>
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
          >  
            {{ $t('buttons.search.label') }}
          </v-btn>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
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
    }
  },
  data () {
    return {
      regular: false,
      date: null,
      menu: false,
      labelOrigin: this.$t('origin'),
      labelDestination: this.$t('destination'),
      locale: this.$i18n.locale
    }
  },
}
</script>
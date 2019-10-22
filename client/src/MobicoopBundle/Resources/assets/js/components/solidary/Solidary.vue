<template>
  <v-content>
    <v-container>
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <h1>{{ $t('title') }}</h1>
        </v-col>
      </v-row>
      <v-row>
        <v-col
          cols="12"
        >
          <p>{{ $t('introduction1') }}</p>
          <p>{{ $t('introduction2') }}</p>
        </v-col>
      </v-row>
      
      <!--solidary-form-->
      
      <solidary-form
        :geo-search-url="geoSearchUrl"
        :user="user"
        :init-regular="dataRegular"
        :punctual-date-optional="punctualDateOptional"
      />
    </v-container>
  </v-content>
</template>

<script>
import {merge} from "lodash";
import Translations from "@translations/components/solidary/Solidary.json";
import TranslationsClient from "@clientTranslations/components/solidary/Solidary.json";
import SolidaryForm from './SolidaryForm'

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  name: "Solidarity",
  components: {
    SolidaryForm
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
  },
}
</script>

<style scoped>

</style>
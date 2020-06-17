<template>
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
        align="center"
      >
        <p>{{ $t('introduction1') }}</p>
        <p>{{ $t('introduction2') }}</p>
      </v-col>
    </v-row>
      
    <!--solidary-form-->
      
    <solidary-form
      :geo-search-url="geoSearchUrl"
      :user="user"
    />
  </v-container>
</template>

<script>
import {merge} from "lodash";
import Translations from "@translations/components/solidary/Solidary.js";
import TranslationsClient from "@clientTranslations/components/solidary/Solidary.js";
import SolidaryForm from './SolidaryForm'

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
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
    }
  }
}
</script>

<style scoped>

</style>
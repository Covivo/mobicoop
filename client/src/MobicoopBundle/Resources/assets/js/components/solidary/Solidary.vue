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
      >
        <p>{{ $t('introduction1') }}</p>
        <p>{{ $t('introduction2') }}</p>
      </v-col>
    </v-row>
      
    <!--solidary-form-->
      
    <solidary-form
      :geo-search-url="geoSearchUrl"
      :user="user"
      :regular="regular"
      :punctual-date-optional="punctualDateOptional"
      :structures="structures"
      :subjects="subjects"
    />
  </v-container>
</template>

<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/solidary/Solidary/";
import SolidaryForm from './SolidaryForm'

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
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
    },
    regular: {
      type: Boolean,
      default: false
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    structures: {
      type: Array,
      default: null
    },
    subjects: {
      type: Array,
      default: null
    }
  },
}
</script>

<style scoped>

</style>
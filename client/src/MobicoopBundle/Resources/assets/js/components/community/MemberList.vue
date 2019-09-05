<template>
  <v-card>
    <v-card-title>
      <v-row>
        <v-col cols="6">
          {{ $t('List of members') }}
        </v-col>
        <v-col cols="6">
          <div class="flex-grow-1" />
          <v-card
            class="ma-3 pa-6"
            outlined
            tile
          >
            <v-text-field
              v-model="search"
              hide-details
              label="Search"
              single-line
            />
          </v-card>
        </v-col>
      </v-row>
    </v-card-title>
    <v-data-table
      :headers="headers"
      :items="users"
      :search="search"
    />
  </v-card>
</template>

<script>

import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/home/HomeSearch.json";
import TranslationsClient from "@clientTranslations/components/home/HomeSearch.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props:{
    users: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      search: '',
      headers: [
        {
          text: 'Id',
          align: 'left',
          sortable: false,
          value: 'id',
        },
        { text: 'Nom', value: 'familyName' },
        { text: 'Prenom', value: 'givenName' },
        { text: 'Telephone', value: 'telephone' },
        { text: 'Status', value: 'status' }
      ]
    }
  },
  computed: {
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/${this.origin.addressLocality}/${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.computedDateFormat}/resultats`;
    },
    searchUnavailable() {
      return (!this.origin || !this.destination || this.loading == true)
    },
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullNumericDate"))
        : moment(new Date()).format(this.$t("ui.i18n.date.format.fullNumericDate"));
    }
  }
}
</script>

<style scoped>

</style>
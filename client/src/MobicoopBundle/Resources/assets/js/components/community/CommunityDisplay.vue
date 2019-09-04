<template>
  <v-card class="mdi-margin">
    <v-card id="community_headers">
      <v-row>
        <v-col
          class="col-4"
        >
          Rejoindre la communauté
        </v-col>
        <v-col
          class="col-8"
        >
          Carte de la communauté avec l'ensemble des membres
        </v-col>
      </v-row>
    </v-card>
    <v-card id="community_body">
      <v-row>
        <v-card class="col-12">
          <v-card-title>
            {{ $t('List of members') }}
            <div class="flex-grow-1" />
            <v-text-field
              v-model="search"
              append-icon="search"
              label="Search"
              single-line
              hide-details
            />
          </v-card-title>
          <v-data-table
            :headers="headers"
            :items="users"
            :search="search"
          />
        </v-card>
      </v-row>
      <v-card><v-row>
        <v-col
                class="col-4"
        >
          <v-card>
            Le communauté c'est
            - inscrits
            - offres de covoiturage
            - mises en relation
            + en V2 nm km covoiturés et CO2 évté depuis début d'année
          </v-card>
        </v-col>
        <v-col
                class="col-4"
        >
          <v-card>
            ils nous ont rejoints
            3 derniers inscrits
          </v-card>
        </v-col>
        <v-col
                class="col-4"
        >
          <v-card>
            Actualité(Administré par la le créateur de la communauté)
          </v-card>
        </v-col>
      </v-row></v-card>

    </v-card>
    <v-card id="community_footer">
      <v-row>
        Module de recherche de trajet
      </v-row>
    </v-card>
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

<template>
  <div class="margin">
    <div id="community_headers">
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
    </div>
    <div id="community_body">
      <v-row>
        <v-col cols="12">
          <v-card
            class="ma-3 pa-6"
            outlined
            tile
          >
            <member-list :users="users" />
          </v-card>
        </v-col>
      </v-row>
      <v-container fluid>
        <v-row>
          <v-col cols="12">
            <v-row
              :align="alignment"
              :justify="justify"
              class="grey lighten-5"
              style="height: 300px;"
            >
              <v-col cols="4">
                <v-card
                  class="ma-3 pa-6"
                  outlined
                  tile
                >
                  Le communauté c'est
                  - inscrits
                  - offres de covoiturage
                  - mises en relation
                  + en V2 nm km covoiturés et CO2 évté depuis début d'année
                </v-card>
              </v-col>
              <v-col cols="4">
                <v-card
                  class="ma-3 pa-6"
                  outlined
                  tile
                >
                  ils nous ont rejoints
                  3 derniers inscrits
                </v-card>
              </v-col>
              <v-col cols="4">
                <v-card
                  class="ma-3 pa-6"
                  outlined
                  tile
                >
                  Actualité(Administré par la le créateur de la communauté)
                </v-card>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-container>
    </div>
    <div id="community_footer">
      <v-row>
        Module de recherche de trajet
      </v-row>
    </div>
  </div>
</template>
<script>

import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/home/HomeSearch.json";
import TranslationsClient from "@clientTranslations/components/home/HomeSearch.json";
import MemberList from "./MemberList";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    MemberList
  },
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

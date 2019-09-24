<template>
  <v-card
    flat
  >
    <v-card-title
      flat
    >
      <v-row>
        <v-col cols="6">
          <p headline>
            Liste des membres
          </p>
        </v-col>
        <v-col cols="6">
          <div class="flex-grow-1" />
          <v-card
            flat
          >
            <v-text-field
              v-model="search"
              hide-details
              label="Rechercher"
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
      :footer-props="{
        'items-per-page-all-text': 'Tous',
        'itemsPerPageText': 'Nombre de lignes par page'
      }"
    >
      <template v-slot:item.action="{ item }">
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-icon
              color="success"
              @click="contactItem(item)"
            >
              mdi-email
            </v-icon>
          </template>
        </v-tooltip>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>

import axios from "axios";
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
    community: {
      type: Object,
      default: null
    },
  },
  data () {
    return {
      search: '',
      dialog: false,
      headers: [
        { text: 'Nom', value: 'familyName' },
        { text: 'Prenom', value: 'givenName' },
        // { text: 'Actions', value: 'action', sortable: false }
      ],
      users: [],
    }
  },
  
  mounted() {
    this.getCommunityMemberList();
    
  },
  methods: {
   
    getCommunityMemberList () {
      axios 
        .get('/community-member-list/'+this.community.id, {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(res => {
          this.users = res.data;
        });
    }
  }
}
</script>

<style scoped>

</style>
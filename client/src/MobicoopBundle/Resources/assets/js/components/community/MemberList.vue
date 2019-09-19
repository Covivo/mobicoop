<template>
  <v-card>
    <v-card-title>
      <v-row>
        <v-col cols="6">
          {{ $t('Liste des membres') }}
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
              :label="$t('Rechercher')"
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
        'items-per-page-all-text': $t('Tous'),
        'itemsPerPageText': $t('Nombre de lignes par page')
      }"
    >
      <template v-slot:item.action="{ item }">
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-icon
              color="green"
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
      dialog: false,
      headers: [
        { text: 'Nom', value: 'familyName' },
        { text: 'Prenom', value: 'givenName' },
        { text: 'Actions', value: 'action', sortable: false }
      ],
      editedIndex: -1,
      editedItem: {
        id:'',
        familyName: '',
        givenName: '',
        telephone: '+33',
        status: 0,
      },
      defaultItem: {
        familyName: '',
        givenName: '',
        telephone: '+33',
        status: 0,
      },
    }
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'Nouveau membre' : 'Edition de la fiche d\'un membre'
    },
  },

  watch: {
    dialog (val) {
      val || this.close()
    },
  },
  methods: {
    editItem (item) {
      this.editedIndex = this.users.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },

    deleteItem (item) {
      const index = this.users.indexOf(item)
      confirm('Are you sure you want to delete this item?') && this.users.splice(index, 1)
    },

    contactItem: function (item) {
      window.location.href = '/utilisateur/messages?to='+item.id
    },

    close () {
      this.dialog = false
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      }, 300)
    },

    save () {
      if (this.editedIndex > -1) {
        Object.assign(this.users[this.editedIndex], this.editedItem)
      } else {
        this.users.push(this.editedItem)
      }
      this.close()
    },
  }
}
</script>

<style scoped>

</style>
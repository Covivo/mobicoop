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
      <template v-slot:top>
        <v-toolbar
          flat
          color="white"
        >
          <v-divider
            class="mx-4"
            inset
            vertical
          />
          <div class="flex-grow-1" />
          <v-dialog
            v-model="dialog"
            max-width="500px"
          >
            <template v-slot:activator="{ on }">
              <v-btn
                color="primary"
                dark
                class="mb-2"
                v-on="on"
              >
                Nouveau membre
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col
                      cols="12"
                      sm="6"
                      md="4"
                    >
                      <v-text-field
                        v-model="editedItem.familyName"
                        label="Nom"
                      />
                    </v-col>
                    <v-col
                      cols="12"
                      sm="6"
                      md="4"
                    >
                      <v-text-field
                        v-model="editedItem.givenName"
                        label="Prenom"
                      />
                    </v-col>
                    <v-col
                      cols="12"
                      sm="6"
                      md="4"
                    >
                      <v-text-field
                        v-model="editedItem.status"
                        label="status"
                      />
                    </v-col>
                    <v-col
                      cols="12"
                      sm="6"
                      md="4"
                    >
                      <v-text-field
                        v-model="editedItem.telephone"
                        label="Telephone"
                      />
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <div class="flex-grow-1" />
                <v-btn
                  color="blue darken-1"
                  text
                  @click="close"
                >
                  {{ $t('Annuler') }}
                </v-btn>
                <v-btn
                  color="blue darken-1"
                  text
                  @click="save"
                >
                  {{ $t('Supprimer') }}
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.action="{ item }">
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-icon
              color="blue"
              @click="editItem(item)"
            >
              mdi-pencil
            </v-icon>
          </template>
          <span>Editer la fiche d'un membre</span>
        </v-tooltip>
        <v-icon
          color="red"
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
        <v-icon
          color="green"
          @click="contactItem(item)"
        >
          mdi-email
        </v-icon>
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
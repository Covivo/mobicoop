<template>
  <div>
    <v-row>
      <v-col
        cols="12"
        style="margin-bottom: 0px!important; padding-bottom: 0px!important;"
      >
        <v-toolbar
          flat
          color="primary"
          dark
        >
          <v-toolbar-title> {{ $t('Communautés disponibles') }}</v-toolbar-title>
        </v-toolbar>
      </v-col>
    </v-row>
    <v-card class="pa-6">
      <v-card-title>
        <v-row>
          <v-col
            cols="6"
          >
            <a :href="paths.community_create">
              <v-btn
                data-v-06c1a31a=""
                type="button"
                color="primary"
                rounded
              >
                Créer une communauté
              </v-btn>
            </a>
          </v-col>
          <v-col
            cols="6"
          >
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
      <v-data-iterator
        :search="search"
        :items="communities"
        :items-per-page.sync="itemsPerPage"
        :footer-props="{
          'items-per-page-options': itemsPerPageOptions,
          'items-per-page-all-text': $t('Tous'),
          'itemsPerPageText': $t('Nombre de lignes par page')
        }"
      >
        <template>
          <v-row>
            <v-col
              v-for="item in communities"
              :key="item.index"
              cols="12"
              class="ma-3 pa-6"
              outlined
              tile
            >
              <v-card>
                <v-row>
                  <v-col cols="3">
                    <v-img
                      src="https://picsum.photos/id/11/500/300"
                      lazy-src="https://picsum.photos/id/11/10/6"
                      aspect-ratio="1"
                      class="grey lighten-2"
                      max-width="200"
                      max-height="150"
                    />
                  </v-col>
                  <v-col cols="6">
                    <v-card-title>
                      <div v-if="item.membersHidden || item.proposalsHidden">
                        <h4>
                          {{ item.name }}
                        </h4>
                      </div>
                      <div v-else>
                        <h4>
                          <a :href="linkToCommunityShow(item)">{{ item.name }}</a>
                        </h4>
                      </div>
                    </v-card-title>
                    <v-divider />
                    <v-list dense>
                      <v-list-item>
                        <v-list-item-content>
                          {{ item.description }}
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                  </v-col>
                  <v-col
                    cols="3"
                    class="text-center"
                  >
                    <div
                      v-if="!item.membersHidden && !item.proposalsHidden && !item.isSecured"
                      class="my-2"
                    >
                      <a href="#">
                        <v-btn
                          color="primary"
                          rounded
                          @click="test"
                        >
                          Voir les annonces
                        </v-btn>
                      </a>
                    </div>
                    <div
                      v-if="!item.membersHidden && !item.proposalsHidden && !item.isSecured"
                      class="my-2"
                    >
                      <a :href="linkToCommunityJoin(item)">
                        <v-btn
                          color="primary"
                          rounded
                        >
                          Rejoindre la communauté
                        </v-btn>
                      </a>
                    </div>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
          </v-row>
        </template>
      </v-data-iterator>
    </v-card>
  </div>
</template>

<script>

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
    communities: {
      type: Array,
      default: null
    },
    paths: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      search: '',
      itemsPerPageOptions: [10, 20, 50, 100, -1],
      itemsPerPage: 10,
      headers: [
        {
          text: 'Id',
          align: 'left',
          sortable: false,
          value: 'id',
        },
        { text: 'Nom', value: 'name' },
        { text: 'Description', value: 'description' },
        { text: 'Image', value: 'logos' }
      ]
    }
  },

  methods:{
    linkToCommunityJoin: function (item) {
      return '/join-community/'+item.id;
    },
    linkToCommunityShow: function (item) {
      return '/community/'+item.id;
    },
    test() {
      console.error(this.communities)
      return 1;
    }
  }
}
</script>

<style scoped>

</style>
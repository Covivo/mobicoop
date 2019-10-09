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
          <v-toolbar-title> {{ $t('communitiesAvailable') }}</v-toolbar-title>
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
                {{ $t('createCommunity') }}
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
                :label="$t('search')"
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
          'items-per-page-all-text': $t('all'),
          'itemsPerPageText': $t('linePerPage')
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
                      v-if="item['images'][0]"
                      :src="item['images'][0]['versions']['square_250']"
                      lazy-src="https://picsum.photos/id/11/10/6"
                      aspect-ratio="1"
                      class="grey lighten-2"
                      max-width="200"
                      max-height="150"
                    />
                    <v-img
                      v-else
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
                        <v-tooltip
                          top
                          color="info"
                        >
                          <template v-slot:activator="{ on }">
                            <h4 v-on="on">
                              {{ item.name }}
                            </h4>
                          </template>
                          <span>{{ $t('protected') }}</span>
                        </v-tooltip>
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
                      <v-tooltip
                        left
                        color="info"
                      >
                        <template v-slot:activator="{ on }">
                          <div
                            v-on="on"
                          >
                            <v-btn
                              color="primary"
                              rounded
                              disabled
                              @click="test"
                            >
                              {{ $t('seeAds') }}
                            </v-btn>
                          </div>
                        </template>
                        <span>{{ $t('ui.infos.notAvailableYet') }}</span>
                      </v-tooltip>
                    </div>
                    <div
                      v-if="!item.membersHidden && !item.proposalsHidden && !item.isSecured"
                      class="my-2"
                    >
                      <v-tooltip
                        left
                        color="info"
                      >
                        <template v-slot:activator="{ on }">
                          <div
                            v-on="on"
                          >
                            <v-btn
                              color="primary"
                              rounded
                              disabled
                            >
                              {{ $t('join') }}
                            </v-btn>
                          </div>
                        </template>
                        <span>{{ $t('ui.infos.notAvailableYet') }}</span>
                      </v-tooltip>
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
import Translations from "@translations/components/community/CommunityList.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityList.json";

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
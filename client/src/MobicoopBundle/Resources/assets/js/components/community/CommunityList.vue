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
          <v-toolbar-title> {{ $t('myCommunities') }}</v-toolbar-title>
        </v-toolbar>
        <v-card class="pa-6">
          <v-data-iterator
            :items="communitiesUser"
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
                  v-for="item in communitiesUser"
                  :key="item.index"
                  cols="12"
                  class="ma-3 pa-6"
                  outlined
                  tile
                >
                  <CommunityListItem :item="item" />
                </v-col>
              </v-row>
            </template>
          </v-data-iterator>
        </v-card>
      </v-col>
    </v-row>

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
                type="button"
                color="secondary"
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
              <CommunityListItem :item="item" />
            </v-col>
          </v-row>
        </template>
      </v-data-iterator>      
    </v-card>
  </div>
</template>

<script>

import { merge } from "lodash";
import Translations from "@translations/components/community/CommunityList.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityList.json";
import CommunityListItem from "@components/community/CommunityListItem";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components:{
    CommunityListItem
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    communities: {
      type: Array,
      default: null
    },
    communitiesUser:{
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
}
</script>

<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
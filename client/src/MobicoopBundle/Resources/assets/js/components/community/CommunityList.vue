<template>
  <div :key="rerenderKey">
    <v-row
      v-if="communitiesUser.length>0"
    >
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
                  <CommunityListItem
                    :item="item"
                    :can-leave="true"
                  />
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
            v-if="canCreate"
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
            :cols="(canCreate) ? 6 : 12"
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
        :server-items-length="totalItems"
        :footer-props="{
          'items-per-page-options': itemsPerPageOptions,
          'items-per-page-all-text': $t('all'),
          'itemsPerPageText': $t('linePerPage')
        }"
        @update:options="updateOptions"
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
    },
    canCreate: {
      type: Boolean,
      default: null
    },
    communitiesView:{
      type: Object,
      default: null
    },
    totalItems:{
      type: Number,
      default: null
    },
  },
  data () {
    return {
      rerenderKey: 0,
      search: '',
      itemsPerPageOptions: [1, 10, 20, 50, 100, -1],
      itemsPerPage: 1,
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
  methods: {
    leaveCommunity(community) {
      var self = this, i = null;
      this.communitiesUser.forEach(function(item, index) {
        if(item.id === community.id){
          self.communities.push(community); // ADD TO AVAILABLE COMMUNITIES
          i = index;
          return;
        }
      });
      this.communitiesUser.splice(i, 1); // REMOVE FROM MY COMMUNITIES
      this.refreshComponent();
    },
    refreshComponent() {
      this.rerenderKey++;
    },
    updateOptions(value){
      console.error("options !");
      console.error(value);
    }
  }
}
</script>

<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>

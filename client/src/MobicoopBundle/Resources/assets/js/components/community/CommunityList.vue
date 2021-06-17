<template>
  <div :key="rerenderKey">
    <v-row>
      <v-col
        v-if="canCreate"
        cols="6"
      >
        <a
          v-if="isLogged"
          :href="paths.community_create"
        >
          <v-btn
            type="button"
            color="secondary"
            rounded
          >
            {{ $t('createCommunity') }}
          </v-btn>
        </a>
      </v-col>
    </v-row>
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
          <v-row v-if="loading">
            <v-skeleton-loader
              v-for="n in 3"
              :key="n"
              ref="skeleton"
              type="list-item-avatar-three-line"
              class="mx-auto"
              width="100%"
            />
          </v-row>
          <v-row v-else>
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
          <v-col>
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
                clearable
                @input="updateSearch"
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
        :no-data-text="$t('noCommunity')"
        :footer-props="{
          'items-per-page-options': itemsPerPageOptions,
          'items-per-page-all-text': $t('all'),
          'itemsPerPageText': $t('linePerPage')
        }"
        :loading="loading"
        @update:options="updateOptions"
      >
        <template>
          <v-row v-if="loading">
            <v-skeleton-loader
              ref="skeleton"
              type="list-item-avatar-three-line"
              class="mx-auto"
              width="100%"
            />
            <v-skeleton-loader
              ref="skeleton"
              type="list-item-avatar-three-line"
              class="mx-auto"
              width="100%"
            />
            <v-skeleton-loader
              ref="skeleton"
              type="list-item-avatar-three-line"
              class="mx-auto"
              width="100%"
            />
          </v-row>
          <v-row v-else>
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
import axios from "axios";
import debounce from "lodash/debounce";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/CommunityList/";
import CommunityListItem from "@components/community/CommunityListItem";

export default {
  components:{
    CommunityListItem
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props:{
    paths: {
      type: Object,
      default: null
    },
    itemsPerPageDefault: {
      type: Number,
      default: 1
    },
    isLogged: {
      type: Boolean,
      default: false
    },
    orderCriteria: {
      type: String,
      default: "name"
    },
    orderWay: {
      type: String,
      default: "asc"
    }
  },
  data () {
    return {
      rerenderKey: 0,
      search: '',
      itemsPerPageOptions: [1, 10, 20, 50, 100],
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
      ],
      communities:[],
      communitiesUser:[],
      canCreate:false,
      communitiesView:null,
      itemsPerPage:this.itemsPerPageDefault,
      totalItems:0,
      page:1,
      loading:false
    }
  },
  mounted() {
    //this.getCommunities();    
  },
  methods: {
    refreshComponent() {
      this.rerenderKey++;
    },
    getCommunities(){
      this.loading = true;
      
      // this.cancelRequest(); // CANCEL PREVIOUS REQUEST
      // this.cancelSource = axios.CancelToken.source();

      let params = {
        'perPage':this.itemsPerPage,
        'page':this.page,
        'search':{
          'name':this.search
        },
        'order':this.orderCriteria,
        'orderWay':this.orderWay,
        'showAllCommunities':true
      }

      axios
        .post(this.$t('urlGetCommunities'),params)
        .then(response => {
          //console.error(response.data);
          if(response.data.communities){
            this.communities = response.data.communities;
            this.communitiesUser = response.data.communitiesUser;
            this.canCreate = response.data.canCreate;
            this.communitiesView = response.data.communitiesView;
            this.totalItems = response.data.totalItems;
          }
          this.loading = false;

        })
        .catch(function (error) {
          console.error(error);
        });          
          
    },
    updateOptions(value){
      this.itemsPerPage = value.itemsPerPage;
      this.page = value.page;
      this.getCommunities();
    },
    updateSearch: debounce(function(value) {
      this.getCommunities();
    }, 1000)
  }
}
</script>

<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>

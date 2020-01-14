<template>
  <v-card
    flat
  >
    <v-card-title
      flat
    >
      <v-row>
        <v-col cols="12">
          <h3 class="headline text-justify font-weight-bold">
            {{ $t('title') }}
          </h3>
        </v-col>
        <!-- For now, the research is hidden. It's not functionnal -->
        <v-col
          cols="12"
          hidden
        >
          <div class="flex-grow-1" />
          <v-card
            flat
          >
            <v-text-field
              v-model="search"
              hide-details
              :label="$t('table.search')"
              single-line
            />
          </v-card>
        </v-col>
      </v-row>
    </v-card-title>
    <v-data-table
      v-if="!hidden && !loading"
      :headers="headers"
      :items="usersShowned"
      :search="search"
      :footer-props="{
        'items-per-page-options': itemsPerPageOptions,
        'items-per-page-all-text': $t('table.all'),
        'itemsPerPageText': $t('table.lineNumber'),
      }"
      :server-items-length="totalItems"
      @update:options="updateOptions"
    >
      <template v-slot:item.action="{ item }">
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-icon
              color="secondary"
              @click="contactItem(item)"
            >
              mdi-email
            </v-icon>
          </template>
        </v-tooltip>
      </template>
    </v-data-table>
    <v-card-text v-else>
      <div v-if="hidden">
        {{ $t('hidden') }}
      </div>
      <div v-else>
        <v-skeleton-loader
          class="mx-auto"
          width="100%"
          type="list-item-three-line"
        />        
      </div>
    </v-card-text>
  </v-card>
</template>

<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/community/CommunityMemberList.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityMemberList.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    communityId: {
      type: Number,
      default: null
    },
    refresh: {
      type: Boolean,
      default: false
    },
    hidden: {
      type: Boolean,
      default: false
    },
    givenUsers: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      firstload:true,
      search: '',
      dialog: false,
      headers: [
        { text: this.$t('table.colTitle.familyName'), value: 'familyName' },
        { text: this.$t('table.colTitle.givenName'), value: 'givenName' },
        { text: this.$t('table.colTitle.actions'), value: 'action', sortable: false },
      ],
      itemsPerPageOptions: [1, 10, 20, 50, 100, -1],
      users: this.givenUsers ? this.givenUsers : [],
      usersShowned:[],
      loading:true,
      totalItems:0
    }
  },
  watch: {
    refresh(){
      (this.refresh) ? this.getCommunityMemberList() : ''
    }
  },
  created(){
    this.getCommunityMemberList();
  },
  methods: {
    getCommunityMemberList () {
      this.loading = true;
      let data = {
        "id":this.communityId
      }
      axios 
        .post(this.$t("urlMembersList"), data)
        .then(res => {
          this.users = res.data.users;
          this.totalItems = res.data.totalItems;
          this.loading = false;
          this.$emit("refreshed");
        });
    },
    contactItem(item){
      this.$emit("contact",item);
    },
    updateOptions(data){
      let page = 0+data.page;
      let startItem = data.itemsPerPage*(page-1);
      let endItem = startItem+data.itemsPerPage;
      this.usersShowned = this.users.slice(startItem,endItem);
    }
  }
}
</script>

<style scoped>

</style>
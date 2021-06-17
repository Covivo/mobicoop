<template>
  <v-card
    flat
  >
    <v-card-title
      flat
    >
      <v-row>
        <v-col cols="12">
          <h3 class="text-h5 text-justify font-weight-bold">
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
      <template v-slot:item.member="{ item }">
        {{ displayUserName(item) }} - <b>{{ displayModerator(item) }}</b><b>{{ displayReferrer(item) }}</b>
      </template> 
      <template v-slot:item.action="{ item }">
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-icon
              color="secondary"
              v-on="on"
              @click="contactItem(item)"
            >
              mdi-email
            </v-icon>
          </template>
          <span>{{ $t('directMessage') }}</span>
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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/CommunityMemberList/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
    },
    directMessage: {
      type: Boolean,
      default: false
    },
  },
  data () {
    return {
      firstload:true,
      search: '',
      dialog: false,
      itemsPerPageOptions: [1, 10, 20, 50, 100, -1],
      users: this.givenUsers ? this.givenUsers : [],
      usersShowned:[],
      loading:true,
      totalItems:0
    }
  },
  computed:{
    headers(){

      let headers = [{ text: this.$t('table.colTitle.familyName'), value: 'member' }];

      if(this.directMessage){
        headers.push({ text: this.$t('table.colTitle.actions'), value: 'action', sortable: false });
      }

      return headers;
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
    },
    displayUserName(user) {
      return  user.givenName + ' ' + user.shortFamilyName;
    },
    displayReferrer(user) {
      if (user.isCommunityReferrer) {
        return this.$t('referrer');
      }
    },
    displayModerator(user) {
      if (user.isCommunityModerator) {
        return this.$t('moderator');
      }
    }
  }
}
</script>
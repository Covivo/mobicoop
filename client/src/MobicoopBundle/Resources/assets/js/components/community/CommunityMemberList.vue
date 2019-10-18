<template>
  <v-card
    flat
  >
    <v-card-title
      flat
    >
      <v-row>
        <v-col cols="12">
          <p headline>
            {{ $t('title') }}
          </p>
        </v-col>
        <v-col cols="12">
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
      v-if="!hidden"
      :headers="headers"
      :items="users"
      :search="search"
      :footer-props="{
        'items-per-page-all-text': $t('table.all'),
        'itemsPerPageText': $t('table.lineNumber')
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
    <v-card-text v-else>
      {{ $t('hidden') }}
    </v-card-text>
  </v-card>
</template>

<script>

import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/CommunityMemberList.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityMemberList.json";

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
    refresh: {
      type: Boolean,
      default: false
    },
    hidden: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      search: '',
      dialog: false,
      headers: [
        { text: this.$t('table.colTitle.familyName'), value: 'familyName' },
        { text: this.$t('table.colTitle.givenName'), value: 'givenName' },
        { text: this.$t('table.colTitle.actions'), value: 'action', sortable: false },
      ],
      users: [],
    }
  },
  watch: {
    refresh(){
      (this.refresh) ? this.getCommunityMemberList() : ''
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
          this.$emit("refreshed");
        });
    },
    contactItem(item){
      this.$emit("contact",item);
    }
  }
}
</script>

<style scoped>

</style>
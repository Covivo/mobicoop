<template>
  <v-card
    flat
    class="mx-6"
  >
    <v-card-title class="headline">
      {{ $t('title') }}
    </v-card-title>
    <v-list
      v-if="!loading"
      shaped
    >
      <v-list-item-group>
        <v-list-item
          v-for="(comUser, i) in lastUsers"
          :key="i"
        >
          <v-list-item-avatar>
            <v-avatar color="tertiary">
              <v-icon light>
                mdi-account-circle
              </v-icon>
            </v-avatar>
          </v-list-item-avatar>
          <v-list-item-content>
            <v-list-item-title v-text="comUser.name" />
            <v-list-item-content v-text="comUser.acceptedDate" />
          </v-list-item-content>
        </v-list-item>
      </v-list-item-group>
    </v-list>
    <div
      v-else
      align="center"
      justify="center"
    >
      <v-progress-circular
        indeterminate
        color="tertiary"
      />
    </div>
  </v-card>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/CommunityLastUsers.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityLastUsers.json";

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
    }
  },
  data () {
    return { 
      lastUsers: null,
      loading: false
    }
  },
  mounted() {
    this.getCommunityLastUsers();
  },
  methods:{
    getCommunityLastUsers() {
      this.loading = true;
      axios 
        .get('/community-last-users/'+this.community.id, {
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(res => {
          this.lastUsers = res.data;
          this.loading = false;
        });
    },
  }
}
</script>
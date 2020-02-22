<template>
  <div>
    <p
      class="headline text-justify text-no-wrap font-weight-bold mt-6"
    >
      {{ $t('title') }}
    </p>

    <div v-if="!hidden">
      <v-list
        v-if="!loading"
        shaped
      >
        <v-list-item-group class="text-end">
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
              <v-list-item-content v-text="comUser.name" />
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
    </div>
    <v-card-text v-else>
      {{ $t('hidden') }}
    </v-card-text>
  </div>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/community/CommunityLastUsers.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityLastUsers.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
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
    },
    givenLastUsers: {
      type: Array,
      default: null
    }
  },
  data () {
    return { 
      lastUsers: this.givenLastUsers ? this.givenLastUsers : null,
      loading: false
    }
  },
  watch: {
    refresh(){
      (this.refresh) ? this.getCommunityLastUsers() : ''
    }
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
          this.$emit("refreshed");
        });
    }
  }
}
</script>
<template>
  <v-main>
    <v-container>
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          <community-infos
            :community="community"
            :url-alt-avatar="urlAltAvatar"
            :avatar-version="avatarVersion"
            :display-description="false"
            :is-widget="true"
          />
        </v-col>
      </v-row>
      <!-- search journey -->
      <p
        class="font-weight-bold"
        align="center"
      >
        {{ $t('title.searchCarpool') }}
      </p>
      <!-- event buttons and map -->
      <v-row
        align="center"
        justify="center"
      >
        <v-col
          col="12"
        >
          <search
            :geo-search-url="geodata.geocompleteuri"
            :user="user"
            :params="params"
            :punctual-date-optional="punctualDateOptional"
            :regular="regular"
            :default-destination="defaultDestination"
            :hide-publish="true"
            :disable-search="disableSearch"
            :show-destination="true"
            :is-widget="true"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/community/Community.json";
import TranslationsClient from "@clientTranslations/components/community/Community.json";
import CommunityInfos from "@components/community/CommunityInfos";
import Search from "@components/carpool/search/Search";
import moment from "moment";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    CommunityInfos,
    Search,
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    geodata: {
      type: Object,
      default: null
    },
    community:{
      type: Object,
      default: null
    },
    avatarVersion: {
      type: String,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      locale: this.$i18n.locale,
      params: { 'communityId' : this.community.id },
      defaultDestination: this.community.address,
    }
  },
  computed: {
    disableSearch() {
      return false;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  }
}
</script>

<style>
  div {
    padding: 0px 3px !important;
  }
  div.row {
    display: block !important;

  }
  div.row p.body-2 {
    font-size: 0.75rem !important;
    line-height: 1rem !important;
    padding: 0px 3px !important;
  }
 </style>
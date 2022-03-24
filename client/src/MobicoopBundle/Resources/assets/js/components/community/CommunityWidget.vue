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
            :display-description="false"
            :is-widget="true"
            :justify-title="justifyTitle"
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
            :geo-complete-results-order="geoCompleteResultsOrder"
            :geo-complete-palette="geoCompletePalette"
            :geo-complete-chip="geoCompleteChip"
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

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/Community/";
import CommunityInfos from "@components/community/CommunityInfos";
import Search from "@components/carpool/search/Search";
import moment from "moment";

export default {
  components: {
    CommunityInfos,
    Search,
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
    },
    token: {
      type: String,
      default: ''
    },
    justifyTitle: {
      type: String,
      default: "text-h5 text-center font-weight-bold",
    },
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
  },
  data () {
    return {
      locale: localStorage.getItem("X-LOCALE"),
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
    this.$store.commit('a/setToken', this.token);
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
  div.row p.text-body-2 {
    font-size: 0.75rem !important;
    line-height: 1rem !important;
    padding: 0px 3px !important;
  }
 </style>

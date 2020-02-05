<template>
  <v-content>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error':'warning'"
      top
    >
      <!--      {{ (errorUpdate)?textSnackError:textSnackOk }}-->
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-container>
      <!-- eventWidget buttons and map -->
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          <!-- Event : avatar, title and description -->
          <event-infos
            :event="event"
            :url-alt-avatar="urlAltAvatar"
            :avatar-version="avatarVersion"
            :display-description="false"
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
            :show-destination="false"
            :is-widget="true"
          />
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/event/Event.json";
import TranslationsClient from "@clientTranslations/components/event/Event.json";
import EventInfos from "@components/event/EventInfos";
import Search from "@components/carpool/search/Search";
import moment from "moment";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
    EventInfos, Search,
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
    users: {
      type: Array,
      default: null
    },
    event:{
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
      search: '',
      loading: false,
      snackbar: false,
      errorUpdate: false,
      isLogged: false,
      domain: true,
      params: { 'eventId' : this.event.id },
      defaultDestination: this.event.address,
    }
  },
  computed: {
    disableSearch() {
      let now = moment();
      if (now > moment(this.event.toDate.date))
        return true;
      else
        return false;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods:{
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
    }
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
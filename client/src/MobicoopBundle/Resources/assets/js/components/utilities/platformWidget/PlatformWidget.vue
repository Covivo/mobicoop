<template>
  <v-main>
    <v-container>
      <!-- <v-row
        justify="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          {{ $t("title") }}
        </v-col>
      </v-row> -->
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          <img
            class="logo"
            :src="$t('widget.urlLogo')"
            alt="Mobicoop"
          >
        </v-col>
      </v-row>
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

import {merge} from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/PlatformWidget/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/PlatformWidget/";
import Search from "@components/carpool/search/Search";
import moment from "moment";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  components: {
    Search
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
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
    }
  },
  data () {
    return {
      locale: this.$i18n.locale,
      params: null,
      defaultDestination: null,
    }
  },
  computed: {
    disableSearch() {
      return false;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    this.$root.token = this.token;
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
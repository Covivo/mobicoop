<template>
  <v-row
    align="center"
    justify="end"
    class="min-width-no-flex mr-1"
  >
    <div v-if="user && carpooler.telephone">
      <v-btn
        v-show="!phoneButtonToggled"
        color="secondary"
        small
        depressed
        fab
        @click="toggleButton"
      >
        <v-icon>
          mdi-phone
        </v-icon>
      </v-btn>
      <v-btn
        v-show="phoneButtonToggled"
        color="secondary"
        small
        dark
        depressed
        rounded
        height="40px"
        @click="toggleButton"
      >
        <v-icon>mdi-phone</v-icon>
        {{ carpooler.telephone }}
      </v-btn>
    </div>
    <div v-if="displayMailBox">
      <v-btn
        color="secondary"
        small
        depressed
        fab
        class="ml-2"
      >
        <v-icon
          @click="openMailBox()"
        >
          mdi-email
        </v-icon>
      </v-btn>
    </div>
  </v-row>
</template>

<script>
import {merge} from "lodash";
import {messages_fr, messages_en} from "@translations/components/carpool/utilities/CarpoolerSummary/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/carpool/utilities/CarpoolerSummary/";
import formData from "../../../utils/request";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props: {
    carpooler: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      default: null
    },
    askId: {
      type: Number,
      default: -99
    },
    displayMailBox: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      phoneButtonToggled: false,
    }
  },
  methods: {
    toggleButton () {
      this.phoneButtonToggled = !this.phoneButtonToggled;
    },
    openMailBox () {
      let lParams = {
        idAsk: this.askId
      };
      formData(this.$t('route.user.message'), lParams);
    }
  }
}
</script>

<style scoped>

</style>
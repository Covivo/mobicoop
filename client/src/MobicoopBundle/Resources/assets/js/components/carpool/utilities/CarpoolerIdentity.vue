<template>
  <v-list-item>
    <!--Carpooler avatar-->
    <v-list-item-avatar
      color="grey darken-3"
      size="50"
    >
      <v-img
        aspect-ratio="2"
        :src="carpooler.avatars[0]"
      />
    </v-list-item-avatar>
    <!--Carpooler data-->
    <v-list-item-content>
      <v-list-item-title class="font-weight-bold">
        {{ carpooler.givenName }} {{ carpooler.shortFamilyName }}
      </v-list-item-title>
      <v-list-item-title>{{ age }} </v-list-item-title>
    </v-list-item-content>
  </v-list-item>
</template>

<script>
import moment from "moment";
import { merge } from "lodash";
import {messages_fr, messages_en} from "@translations/components/carpool/utilities/CarpoolerSummary/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/carpool/utilities/CarpoolerSummary/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    },
  },
  props: {
    carpooler: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      locale: this.$i18n.locale
    }
  },
  computed: {
    age (){
      if (this.carpooler.birthYear) {
        return moment().diff(moment([this.carpooler.birthYear]),'years') + ' ' + this.$t("birthYears");
      } else {
        return null;
      }
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  }
}
</script>

<style scoped>

</style>
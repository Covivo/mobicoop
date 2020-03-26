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
import Translations from "@translations/components/carpool/utilities/CarpoolerSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/utilities/CarpoolerSummary.json";


let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
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
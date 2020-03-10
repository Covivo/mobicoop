<template>
  <v-content>
    <v-container>
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <h1>{{ $t('title') }}</h1>
        </v-col>
      </v-row>
      <contact-form
        :user="user"
        :contact-types="contactTypes"
      />
    </v-container>
  </v-content>
</template>

<script>
import {merge} from "lodash";
import Translations from "@translations/components/contact/Contact.json";
import TranslationsClient from "@clientTranslations/components/contact/Contact.json";
import ContactForm from "@components/contact/ContactForm.vue"

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    ContactForm
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    contactTypes: {
      type: Object,
      default: null
    }
  },
  data () {
    return {

    }
  }
}
</script>

<style scoped>

</style>

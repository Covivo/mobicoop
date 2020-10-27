<template>
  <v-main>
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
  </v-main>
</template>

<script>
import {merge} from "lodash";
import {messages_fr, messages_en} from "@translations/components/contact/Contact/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/contact/Contact/";
import ContactForm from "@components/contact/ContactForm.vue"

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
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

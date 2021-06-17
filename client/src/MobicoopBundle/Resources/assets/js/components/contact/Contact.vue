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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/contact/Contact/";
import ContactForm from "@components/contact/ContactForm.vue"

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
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

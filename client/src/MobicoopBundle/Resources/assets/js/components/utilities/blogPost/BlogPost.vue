<template>
  <v-main>
    <v-container>
      <v-row
        justify="center"
      >
        <v-col
          cols="4"
          md="5"
          align="center"
          class="justify-center"
        >
          <h3 class="font-weight-bold secondary--text">
            {{ rss.item[0].title }}
          </h3>
          <br>
          <v-img :src="image" />
          <br>
          <p>
            {{ description }}
          </p>
          <a
            href="https://blog.covoiturage-grandlyon.com"
            target="_blank"
          >Lire La suite</a>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>

<script>
import {messages_en, messages_fr} from "@translations/components/utilities/BlogPost/";

export default {
  components: {
  },
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  props:{
    rss:{
      type: Object,
      default: null
    }
  },
  data () {
    return {
      locale: this.$i18n.locale,
      description: this.rss.item[0].description.slice(0,255)+' ...'
    }
  },
  methods:{
  }
}
</script>

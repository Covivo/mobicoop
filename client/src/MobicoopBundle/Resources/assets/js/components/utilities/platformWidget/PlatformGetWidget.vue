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
          class="justify-center mt-10"
        >
          <iframe
            :src="$t('widget.externalRoute')"
            width="100%"
            height="640px"
            frameborder="0"
            scrolling="no"
          />
        </v-col>
        <v-col
          cols="8"
          md="7"
          class="mt-12"
        >
          <v-row class="mt-12">
            <h4>{{ $t('widget.textDetails.title') }}</h4>
            <p
              class="mt-8"
              v-html="$t('widget.textDetails.p1')"
            />
            <p v-html="$t('widget.textDetails.p2', {'url':getUrl()})" />
            <p v-html="$t('widget.textDetails.p3')" />
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/PlatformWidget/";

export default {
  components: {
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props:{
    community:{
      type: Object,
      default: null
    },
  },
  methods:{
    getUrl() {
      return window.location.protocol +"//"+ window.location.host + this.$t('widget.externalRoute');
    }
  }
}
</script>

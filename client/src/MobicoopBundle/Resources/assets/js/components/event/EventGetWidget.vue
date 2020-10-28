<template>
  <v-main>
    <v-container>
      <!-- eventGetWidget buttons and map -->
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
            :src="$t('buttons.widget.externalRoute', {'id':event.id})"
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
            <h4>{{ $t('buttons.widget.textDetails.title') }}</h4>
            <p
              class="mt-8"
              v-html="$t('buttons.widget.textDetails.p1')"
            />
            <p v-html="$t('buttons.widget.textDetails.p2', {'url':getUrl()})" />
            <p v-html="$t('buttons.widget.textDetails.p3')" />
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
<script>

import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/event/Event/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/event/Event/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  components: {
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    },
  },
  props:{
    event:{
      type: Object,
      default: null
    }
  },
  methods:{
    getUrl() {
      return window.location.protocol +"//"+ window.location.host + this.$t('buttons.widget.externalRoute', {'id':this.event.id});
    }
  }
}
</script>

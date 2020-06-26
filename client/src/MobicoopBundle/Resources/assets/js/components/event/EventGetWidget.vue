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
          class="justify-center"
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

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/event/Event.json";
import TranslationsClient from "@clientTranslations/components/event/Event.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
  },
  i18n: {
    messages: TranslationsMerged,
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

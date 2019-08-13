<template>
  <v-container
    fluid
    class="pa-0 text-center"
  >
    <v-layout
      wrap
      align-center
      justify-center
    >
      <v-flex
        xs10
      >
        <div class="text-center">
          <div>TODO make the summary</div>
          <div>
            Rappel Trajet
          </div>
          <div>
            Mon message aux passagers
            <v-textarea
              class="my-2"
              name="input-7-1"
              label="Mon message aux passagers"
              value=""
              placeholder="Laissez un petit message ..."
            />
          </div>
          <div class="my-2">
            Carte du trajet
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/AdSummary.json";
import TranslationsClient from "@clientTranslations/components/carpool/AdSummary.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
  },
  props: {
  },
  data() {
    return {
    };
  },
  computed: {
  },
  methods: {
  }
};
</script>
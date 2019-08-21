<template>
  <v-footer
    height="auto"
    color="primary"
  >
    <v-layout
      justify-center
      row
    >
      <a
        href="https://www.facebook.com/Mobicoop/"
        target="_blank"
      >
        <img
          src="/images/footer/facebook.svg"
          width="50px"
          height="50px"
        >
      </a>
      <a
        href="https://twitter.com/MobicoopCovoit?lang=fr"
        target="_blank"
      >
        <img
          src="/images/footer/twitter.svg"
          width="50px"
          height="50px"
        >
      </a>
      <a
        href="https://www.instagram.com/mobicoopcovoiturage/"
        target="_blank"
      >
        <img
          src="/images/footer/instagram.svg"
          width="50px"
          height="50px"
        >
      </a>
      <a
        href="https://framasphere.org/people/9bfd3a40f8fb0134e71f2a0000053625"
        target="_blank"
      >
        <img
          href="https://framasphere.org/people/9bfd3a40f8fb0134e71f2a0000053625"
          target="_blank"
          src="/images/footer/framasphere.svg"
          width="50px"
          height="50px"
        >
      </a>
      <v-flex
        py-4
        text-center
        xs12
      >
        <strong>Mobicoop</strong> <v-chip
          class="ma-2"
        >
          {{ version }}
        </v-chip> by Mobicoop team. The source code is licensed AGPL3. The website content is licensed AGPL3 too
      </v-flex>
    </v-layout>
  </v-footer>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/base/MFooter.json";
import TranslationsClient from "@clientTranslations/components/base/MFooter.json";
import Package from '@root/package.json'

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  data() {
    return {
      version:Package.version
    }
  }
};
</script>
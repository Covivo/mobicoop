<template>
  <v-footer
    height="auto"
    padless
    tile
    color="primary"
  >
    <v-container
      fluid
      class="pa-0"
    >
      <v-row
        justify="center"
      >
        <a
          href="/"
          class="d-flex align-center my-14"
        >
          <img
            class="logo"
            :src="imageLink + 'MOBICOOP_LOGO-V1 Blanc.svg'"
            alt="Mobicoop"
            height="50"
            width="280"
            contain
            eager
          >
        </a>
      </v-row>
      <v-row
        justify="center"
        dense
        class="pa-5"
      >
        <!-- first col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text text-h5 font-weight-bold">
            La coopérative
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >Qui sommes-nous ? </a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >Nous rejoindre </a>
          </v-card-text>

          <v-card-text class="my-0 pt-10 pb-0 white--text text-h5 font-weight-bold">
            Les services Mobicoop
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="https://pro.mobicoop.fr/"
              style="text-decoration:none;"
              class="white--text"
            >pro.mobicoop.fr</a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="https://www.covievent.org/"
              style="text-decoration:none;"
              class="white--text"
            >www.covievent.org</a>
          </v-card-text>
        </v-col>
        <!-- end first col -->

        <!-- second col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text text-h5 font-weight-bold">
            Les outils
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >La carte des aires de covoiturage </a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >FAQ </a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >CGU </a>
          </v-card-text>

          <v-card-text class="my-0 pt-3 pb-0 white--text text-h5 font-weight-bold">
            Contacts
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >Hotline</a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >Presse</a>
          </v-card-text>
        </v-col>
        <!-- end second col -->

        <!-- third col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text text-h5 font-weight-bold">
            Besoin d’une solution de covoiturage pour votre structure ?
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              href="#"
              style="text-decoration:none;"
              class="white--text"
            >Créez une communauté sur mobicoop.fr !</a>
          </v-card-text>

          <v-card-text class="my-0 pt-10 pb-0 white--text subtitle font-weight-black">
            TÉLÉCHARGEZ L’APPLICATION MOBICOOP
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <v-row>
              <a
                :href="$t('urlIos')"
                target="_blank"
              >
                <v-img
                  class="ml-2"
                  contain
                  :src="$t('urlIosLogo')"
                  max-width="200px"
                />
              </a>
              <a
                :href="$t('urlAndroid')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlAndroidLogo')"
                  max-width="200px"
                />
              </a>
            </v-row>
          </v-card-text>
        </v-col>
        <!-- end third col -->
      </v-row>
      <v-row
        class="my-4"
        text-center
        justify="center"
        align="center"
      >
        <strong>Mobicoop</strong> <v-chip
          class="ma-2"
        >
          {{ version }}
        </v-chip> by Mobicoop team. The source code is licensed AGPL3. The website content is licensed AGPL3 too
      </v-row>
    </v-container>
  </v-footer>
</template>

<script>
import { merge } from "lodash";
import {messages_en, messages_fr} from "@translations/components/base/MFooter/";
import {messages_client_en, messages_client_fr} from "@clientTranslations/components/base/MFooter/";
import Package from '@root/package.json'

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  data() {
    return {
      version:Package.version,
      imageLink: "/images/pages/home/"

    }
  }
};
</script>

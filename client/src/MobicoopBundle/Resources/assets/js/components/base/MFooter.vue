<template>
  <v-footer
    height="auto"
    padless
    tile
    class="mt-n4"
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
            {{ $t('category.cooperative') }}
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('cooperative.talkAboutUs.link')"
              style="text-decoration:none;"
              class="white--text"
            >              
              {{ $t('cooperative.talkAboutUs.title') }}
            </a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('cooperative.joinUs.link')"
              style="text-decoration:none;"
              class="white--text"
            >
              {{ $t('cooperative.joinUs.title') }}
            </a>
          </v-card-text>

          <v-card-text class="my-0 pt-10 pb-0 white--text text-h5 font-weight-bold">
            {{ $t('category.services') }}
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('services.mobicoop.link')"
              style="text-decoration:none;"
              class="white--text"
            >
              {{ $t('services.mobicoop.title') }}
            </a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('services.covievent.link')"
              style="text-decoration:none;"
              class="white--text"
            >
              {{ $t('services.covievent.title') }}
            </a>
          </v-card-text>
        </v-col>
        <!-- end first col -->

        <!-- second col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text text-h5 font-weight-bold">
            {{ $t('category.tools') }}
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('tools.map.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.map.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('tools.events.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.events.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('tools.faq.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.faq.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('tools.cgu.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('tools.cgu.title') }}</a>
          </v-card-text>

          <v-card-text class="my-0 pt-3 pb-0 white--text text-h5 font-weight-bold">
            {{ $t('category.contacts') }}
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('contacts.hotline.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('contacts.hotline.title') }}</a>
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('contacts.presse.link')"
              style="text-decoration:none;"
              class="white--text"
            >{{ $t('contacts.presse.title') }}</a>
          </v-card-text>
        </v-col>
        <!-- end second col -->

        <!-- third col -->
        <v-col cols="3">
          <v-card-text class="my-0 py-0 white--text text-h5 font-weight-bold">
            {{ $t('category.need') }}
          </v-card-text>
          <v-card-text class="my-0 py-0 white--text text-h6">
            <a
              :href="$t('need.community.link')"
              style="text-decoration:none;"
              class="white--text"
            >
              {{ $t('need.community.title') }}
            </a>
          </v-card-text>

          <v-card-text class="my-0 pt-10 pb-0 white--text subtitle font-weight-black">
            {{ $t('category.download') }}
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

        <!-- fourth col -->
        <v-col
          cols="1"
          justify="center"
        >
          <v-card-text class="my-0 py-0 white--text text-h6">
            <v-row justify="center">
              <a
                :href="$t('urlInstagram')"
                target="_blank"
              >
                <v-img
                  class="ml-2"
                  contain
                  :src="$t('urlInstagramLogo')"
                  max-width="75px"
                />
              </a>
              <a
                :href="$t('urlFacebook')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlFacebookLogo')"
                  max-width="75px"
                />
              </a>
              <a
                :href="$t('urlTwitter')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlTwitterLogo')"
                  max-width="75px"
                />
              </a>
              <a
                :href="$t('urlFramasphere')"
                target="_blank"
              >
                <v-img
                  contain
                  :src="$t('urlFramasphereLogo')"
                  max-width="75px"
                />
              </a>
            </v-row>
          </v-card-text>
          <v-col>
            <!-- end col -->
          </v-col>
        </v-col>
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
